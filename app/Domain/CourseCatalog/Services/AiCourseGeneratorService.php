<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\Ai\Services\OpenAiChatService;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Models\AiPrompt;
use Illuminate\Support\Str;

class AiCourseGeneratorService
{
    public function __construct(
        private readonly OpenAiChatService $openAi,
        private readonly AiCourseDraftNormalizerService $draftNormalizer,
        private readonly AiCourseDraftMerger $draftMerger,
    ) {}

    /**
     * @return array{ok: bool, draft?: array<string, mixed>, error?: string}
     */
    public function generateDraft(string $brief, ?int $templatePromptId = null): array
    {
        $settings = AiSetting::singleton();
        if (! $settings->hasOpenAiApiKey()) {
            return ['ok' => false, 'error' => __('Kein OpenAI API-Key in den KI-Einstellungen hinterlegt.')];
        }

        $template = '';
        if ($templatePromptId !== null) {
            $p = AiPrompt::query()->whereKey($templatePromptId)->where('is_active', true)->first();
            if ($p !== null && $p->use_case === PromptUseCase::CourseCreation->value) {
                $template = $p->body."\n\n";
            }
        }

        $instruction = $template.'Erzeuge einen Kursentwurf basierend auf folgender Beschreibung. '
            .'Antworte NUR mit einem JSON-Objekt (kein Markdown, kein Code-Fence) mit exakt diesen Schlüsseln: '
            .'title (string), slug (string, nur a-z0-9 und Bindestriche), short_description (string, mindestens 20 Zeichen), '
            .'long_description (string oder leer), duration_days (integer oder null), price (number oder null), '
            .'language_code (string, z.B. de), seo_title (string oder leer), meta_description (string oder leer). '
            ."Beschreibung des gewünschten Kurses:\n\n".$brief;

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
            return ['ok' => false, 'error' => __('Die KI hat kein gültiges JSON geliefert.')];
        }

        $draft = $this->normalizeDraft($parsed);

        return ['ok' => true, 'draft' => $draft];
    }

    /**
     * Full structured draft from the compiled prompt (admin workflow).
     *
     * @return array{ok: bool, draft_payload?: array<string, mixed>, error?: string, raw_reply?: string}
     */
    public function generateFullStructuredDraft(string $compiledPrompt): array
    {
        $settings = AiSetting::singleton();
        if (! $settings->hasOpenAiApiKey()) {
            return ['ok' => false, 'error' => __('Kein OpenAI API-Key in den KI-Einstellungen hinterlegt.')];
        }

        $instruction = rtrim($compiledPrompt)."\n\n".$this->fullStructuredJsonInstruction();

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
            return ['ok' => false, 'error' => __('Die KI hat kein gültiges JSON geliefert.'), 'raw_reply' => $result['reply']];
        }

        $draftPayload = $this->draftNormalizer->normalizeFromAiJson($parsed);

        return ['ok' => true, 'draft_payload' => $draftPayload, 'raw_reply' => $result['reply']];
    }

    /**
     * Regenerate one logical section; merge into existing draft.
     *
     * @param  array<string, mixed>  $draftPayload
     * @return array{ok: bool, draft_payload?: array<string, mixed>, error?: string}
     */
    public function regenerateSection(string $section, array $draftPayload, string $hint): array
    {
        $settings = AiSetting::singleton();
        if (! $settings->hasOpenAiApiKey()) {
            return ['ok' => false, 'error' => __('Kein OpenAI API-Key in den KI-Einstellungen hinterlegt.')];
        }

        $hint = trim($hint);
        $ctx = json_encode($draftPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $instruction = "Aktueller Kurs-Entwurf (JSON-Kontext):\n{$ctx}\n\n"
            .'Abschnitt zum Neu-Generieren: '.$section."\n"
            ."Redaktioneller Hinweis:\n{$hint}\n\n"
            .$this->sectionJsonInstruction($section);

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
            return ['ok' => false, 'error' => __('Die KI hat kein gültiges JSON geliefert.')];
        }

        $merged = $this->draftMerger->merge($section, $draftPayload, $parsed);

        return ['ok' => true, 'draft_payload' => $merged];
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    public function normalizeDraft(array $parsed): array
    {
        $title = trim((string) ($parsed['title'] ?? 'Neuer Kurs'));
        $slug = trim((string) ($parsed['slug'] ?? ''));
        if ($slug === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $slug = Str::slug($title);
            if ($slug === '') {
                $slug = 'kurs-'.Str::lower(Str::random(8));
            }
        }

        $short = trim((string) ($parsed['short_description'] ?? ''));
        if (mb_strlen($short) < 20) {
            $short = $short.str_repeat('.', max(0, 20 - mb_strlen($short)));
        }

        $long = trim((string) ($parsed['long_description'] ?? ''));

        $duration = $parsed['duration_days'] ?? null;
        $durationDays = $duration !== null && $duration !== '' ? (int) $duration : null;

        $price = $parsed['price'] ?? null;
        $priceVal = $price !== null && $price !== '' ? (float) $price : null;

        $lang = trim((string) ($parsed['language_code'] ?? 'de'));
        if ($lang === '') {
            $lang = 'de';
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'short_description' => $short,
            'long_description' => $long !== '' ? $long : null,
            'duration_days' => $durationDays,
            'price' => $priceVal,
            'language_code' => $lang,
            'seo_title' => trim((string) ($parsed['seo_title'] ?? '')),
            'meta_description' => trim((string) ($parsed['meta_description'] ?? '')),
        ];
    }

    private function fullStructuredJsonInstruction(): string
    {
        return <<<'TXT'
Antworte NUR mit einem JSON-Objekt (kein Markdown, kein Code-Fence). Nutze folgende Schlüssel (optional, falls nicht bekannt: null oder leere Arrays):

title (string), slug (string, nur a-z0-9 und Bindestriche), subtitle (string|null),
short_description (string, mindestens 20 Zeichen), long_description (string|null),
target_audience_text (string|null), prerequisites_text (string|null),
duration_days (int|null), language_code (string), currency_code (string, z.B. EUR),
price (number|null), delivery_format ("online"|"presence"|"hybrid"|null),
lessons_count (int|null), min_participants (int|null), instructor_name (string|null),
certificate_label (string|null), is_featured (boolean),
booking_url (string|null), offer_url (string|null),
primary_category_slug (string|null) — Slug einer existierenden Kategorie,
tag_slugs (array von strings) — Slugs existierender Tags,
audience_slugs (array von strings) — Slugs existierender Zielgruppen,
difficulty_level_code (string|null) — code aus difficulty_levels,
modules (array von Objekten: title, description, duration_hours, sort_order),
objectives (array von Objekten: objective_text, sort_order),
prerequisites (array von Objekten: prerequisite_text, sort_order),
faqs (array von Objekten: question, answer, sort_order),
seo_title, meta_description, focus_keyword, canonical_url (optional),
robots_index, robots_follow (boolean oder 0/1), og_title, og_description (optional).
TXT;
    }

    private function sectionJsonInstruction(string $section): string
    {
        return 'Antworte NUR mit einem JSON-Objekt (kein Markdown, kein Code-Fence), das nur die Felder für diesen Abschnitt enthält '
            .'(gleiche Feldnamen wie im Gesamtentwurf). Abschnitt: '.$section.'.';
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
