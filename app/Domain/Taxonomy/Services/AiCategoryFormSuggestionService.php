<?php

namespace App\Domain\Taxonomy\Services;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\Ai\Services\OpenAiChatService;
use App\Domain\CourseCatalog\Services\PromptPlaceholderInterpolationService;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Models\AiPrompt;

/**
 * Fills empty category form fields (description, SEO, parent) via OpenAI using optional prompt templates.
 */
class AiCategoryFormSuggestionService
{
    public function __construct(
        private readonly OpenAiChatService $openAi,
        private readonly PromptPlaceholderInterpolationService $interpolation,
    ) {}

    /**
     * @param  array<string, mixed>  $input  name, slug, description?, parent_id?, status?, seo?, category_id?
     * @param  list<int>  $allowedParentIds
     * @return array{ok: bool, filled?: array<string, mixed>, error?: string, warnings?: list<string>, raw_reply?: string}
     */
    public function finalize(array $input, ?int $promptId, array $allowedParentIds): array
    {
        $settings = AiSetting::singleton();
        if (! $settings->hasOpenAiApiKey()) {
            return ['ok' => false, 'error' => __('Kein OpenAI API-Key in den KI-Einstellungen hinterlegt.')];
        }

        $templatePrompt = null;
        if ($promptId !== null) {
            $templatePrompt = AiPrompt::query()
                ->whereKey($promptId)
                ->where('is_active', true)
                ->first();
            if ($templatePrompt === null || $templatePrompt->use_case !== PromptUseCase::CategoryManagement->value) {
                return ['ok' => false, 'error' => __('Der gewählte Prompt ist nicht für Kategorien verfügbar.')];
            }
        }

        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        if ($name === '' || $slug === '') {
            return ['ok' => false, 'error' => __('Name und Slug sind erforderlich.')];
        }

        $description = trim((string) ($input['description'] ?? ''));
        $parentId = isset($input['parent_id']) && $input['parent_id'] !== '' && $input['parent_id'] !== null
            ? (int) $input['parent_id'] : null;
        $status = trim((string) ($input['status'] ?? 'draft'));
        $seo = is_array($input['seo'] ?? null) ? $input['seo'] : [];

        $emptyHints = $this->collectEmptyFieldHints($description, $seo, $parentId);
        if ($emptyHints === []) {
            return [
                'ok' => true,
                'filled' => [],
                'warnings' => [__('Alle unterstützten Felder sind bereits befüllt.')],
            ];
        }

        $allowedSet = array_flip(array_map('intval', $allowedParentIds));

        $promptBody = $this->resolvePromptBody($templatePrompt, [
            'category_name' => $name,
            'category_slug' => $slug,
            'category_description' => $description,
            'category_status' => $status,
            'current_parent_id' => $parentId !== null ? (string) $parentId : '',
            'empty_fields_list' => implode(', ', $emptyHints),
            'current_seo_json' => json_encode($seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'allowed_parent_ids_json' => json_encode(array_values($allowedParentIds), JSON_UNESCAPED_UNICODE),
        ]);

        $instruction = $this->wrapJsonInstruction($promptBody, $name, $slug, $description, $status, $parentId, $seo, $emptyHints, $allowedParentIds);

        $result = $this->openAi->sendChatMessage(
            (string) $settings->openai_api_key,
            (string) ($settings->openai_base_url ?: 'https://api.openai.com/v1'),
            (string) ($settings->default_model ?: 'gpt-4o-mini'),
            $instruction
        );

        if (! $result['ok'] || empty($result['reply'])) {
            return ['ok' => false, 'error' => $result['error'] ?? __('KI-Antwort fehlgeschlagen.')];
        }

        $parsed = $this->parseJsonFromReply($result['reply']);
        if ($parsed === null) {
            return [
                'ok' => false,
                'error' => __('Ungültiges JSON in der KI-Antwort.'),
                'raw_reply' => $result['reply'],
            ];
        }

        $warnings = [];
        $filled = $this->mergeFilledOnly(
            $description,
            $seo,
            $parentId,
            $parsed,
            $allowedSet,
            $warnings
        );

        return [
            'ok' => true,
            'filled' => $filled,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<string, string|null>  $placeholders
     */
    private function resolvePromptBody(?AiPrompt $prompt, array $placeholders): string
    {
        if ($prompt !== null) {
            $stringPlaceholders = [
                'category_name' => (string) ($placeholders['category_name'] ?? ''),
                'category_slug' => (string) ($placeholders['category_slug'] ?? ''),
                'category_description' => (string) ($placeholders['category_description'] ?? ''),
                'category_status' => (string) ($placeholders['category_status'] ?? ''),
                'current_parent_id' => (string) ($placeholders['current_parent_id'] ?? ''),
                'empty_fields_list' => (string) ($placeholders['empty_fields_list'] ?? ''),
                'current_seo_json' => (string) ($placeholders['current_seo_json'] ?? '{}'),
                'allowed_parent_ids_json' => (string) ($placeholders['allowed_parent_ids_json'] ?? '[]'),
            ];

            return $this->interpolation->interpolate($prompt->body, $stringPlaceholders);
        }

        return $this->defaultPromptBody();
    }

    private function defaultPromptBody(): string
    {
        return <<<'TXT'
Du unterstützt eine Kategorie-Administration. Du erhältst Kontext und eine Liste leerer Felder.
Fülle nur inhaltlich sinnvolle, knappe deutsche Texte. Keine Platzhalter wie „TBD“.
SEO: max. 200 Zeichen für Beschreibung der Kategorie (description), Meta-Description max. 1000 Zeichen.
TXT;
    }

    /**
     * @param  list<string>  $emptyHints
     * @param  list<int>  $allowedParentIds
     */
    private function wrapJsonInstruction(
        string $promptBody,
        string $name,
        string $slug,
        string $description,
        string $status,
        ?int $parentId,
        array $seo,
        array $emptyHints,
        array $allowedParentIds,
    ): string {
        $ctx = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'parent_id' => $parentId,
            'seo' => $seo,
            'empty_fields' => $emptyHints,
            'allowed_parent_ids' => array_values($allowedParentIds),
        ];
        $ctxJson = json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<TXT
{$promptBody}

Kontext (JSON):
{$ctxJson}

Antworte NUR mit einem JSON-Objekt (kein Markdown außerhalb, kein Code-Fence):
{
  "description": string|null,
  "seo": {
    "seo_title": string|null,
    "meta_description": string|null,
    "canonical_url": string|null,
    "og_title": string|null,
    "og_description": string|null,
    "schema_json": string|null
  },
  "suggested_parent_id": number|null,
  "parent_suggestion_rationale": string|null
}

Regeln:
- NUR Felder füllen, die in "empty_fields" genannt sind; sonst null.
- suggested_parent_id: nur aus allowed_parent_ids oder null; sonst null.
- parent_suggestion_rationale: kurz auf Deutsch, warum diese Parent-Kategorie passt (oder warum Hauptkategorie).
- description max. 200 Zeichen.
- schema_json: nur wenn leer und gewünscht; gültiges JSON-String oder null.
TXT;
    }

    /**
     * @return list<string>
     */
    private function collectEmptyFieldHints(string $description, array $seo, ?int $parentId): array
    {
        $hints = [];
        if ($description === '') {
            $hints[] = 'description';
        }
        foreach (['seo_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'schema_json'] as $key) {
            $v = trim((string) ($seo[$key] ?? ''));
            if ($v === '') {
                $hints[] = 'seo.'.$key;
            }
        }
        if ($parentId === null) {
            $hints[] = 'parent_id';
        }

        return $hints;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @param  array<int, bool>  $allowedSet
     * @param  list<string>  $warnings
     * @return array<string, mixed>
     */
    private function mergeFilledOnly(
        string $description,
        array $seo,
        ?int $parentId,
        array $parsed,
        array $allowedSet,
        array &$warnings
    ): array {
        $filled = [];

        if ($description === '') {
            $d = trim((string) ($parsed['description'] ?? ''));
            if ($d !== '') {
                $filled['description'] = mb_substr($d, 0, 200);
            }
        }

        $seoOut = [];
        $parsedSeo = is_array($parsed['seo'] ?? null) ? $parsed['seo'] : [];
        foreach (['seo_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'schema_json'] as $key) {
            $cur = trim((string) ($seo[$key] ?? ''));
            if ($cur !== '') {
                continue;
            }
            $v = $parsedSeo[$key] ?? null;
            if ($v === null || $v === '') {
                continue;
            }
            $s = trim((string) $v);
            if ($key === 'meta_description' || $key === 'og_description') {
                $s = mb_substr($s, 0, 1000);
            } elseif ($key === 'seo_title' || $key === 'og_title') {
                $s = mb_substr($s, 0, 255);
            } elseif ($key === 'canonical_url') {
                if (! filter_var($s, FILTER_VALIDATE_URL)) {
                    $warnings[] = __('Canonical-URL wurde vom Modell ignoriert (ungültig).');

                    continue;
                }
            } elseif ($key === 'schema_json') {
                try {
                    json_decode($s, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $warnings[] = __('Schema.org JSON wurde ignoriert (ungültig).');

                    continue;
                }
            }
            $seoOut[$key] = $s;
        }
        if ($seoOut !== []) {
            $filled['seo'] = $seoOut;
        }

        if ($parentId === null) {
            $sid = $parsed['suggested_parent_id'] ?? null;
            if ($sid !== null && $sid !== '') {
                $pid = (int) $sid;
                if (isset($allowedSet[$pid])) {
                    $filled['parent_id'] = $pid;
                } else {
                    $warnings[] = __('Vorgeschlagene übergeordnete Kategorie ist nicht erlaubt — ignoriert.');
                }
            }
        }

        $rationale = trim((string) ($parsed['parent_suggestion_rationale'] ?? ''));
        if ($rationale !== '') {
            $filled['parent_suggestion_rationale'] = $rationale;
        }

        return $filled;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonFromReply(string $reply): ?array
    {
        $trim = trim($reply);
        if (str_starts_with($trim, '```')) {
            $trim = preg_replace('/^```[a-zA-Z0-9]*\s*/', '', $trim) ?? $trim;
            $trim = preg_replace('/\s*```$/', '', $trim) ?? $trim;
            $trim = trim($trim);
        }

        try {
            $decoded = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
