<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\Ai\Services\OpenAiChatService;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;

/**
 * Second OpenAI call after the main course draft: pick primary category and audiences from existing taxonomy only.
 */
class AiCourseTaxonomySuggestionService
{
    public function __construct(
        private readonly OpenAiChatService $openAi
    ) {}

    /**
     * Fills primary_category_id / audience_ids when still empty, using only existing slugs from the database.
     *
     * @param  array<string, mixed>  $draftPayload
     * @return array<string, mixed>
     */
    public function applySuggestionsIfNeeded(array $draftPayload, string $brief): array
    {
        $needCategory = empty($draftPayload['primary_category_id']);
        $audienceIds = $draftPayload['audience_ids'] ?? [];
        $needAudiences = ! is_array($audienceIds) || $audienceIds === [];

        if (! $needCategory && ! $needAudiences) {
            return $draftPayload;
        }

        $settings = AiSetting::singleton();
        if (! $settings->hasOpenAiApiKey()) {
            return $draftPayload;
        }

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'parent_id']);

        $audiences = Audience::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        if ($categories->isEmpty() && $audiences->isEmpty()) {
            return $draftPayload;
        }

        $instruction = $this->buildPrompt($brief, $draftPayload, $categories, $audiences);

        $result = $this->openAi->sendChatMessage(
            (string) $settings->openai_api_key,
            (string) ($settings->openai_base_url ?: 'https://api.openai.com/v1'),
            (string) ($settings->default_model ?: 'gpt-4o-mini'),
            $instruction
        );

        if (! $result['ok'] || empty($result['reply'])) {
            $draftPayload['ai_taxonomy_warning'] = $result['error'] ?? __('Taxonomie-Vorschlag fehlgeschlagen.');

            return $draftPayload;
        }

        $parsed = $this->parseJsonFromReply($result['reply']);
        if ($parsed === null) {
            $draftPayload['ai_taxonomy_warning'] = __('Ungültiges JSON bei Taxonomie-Zuordnung.');
            $draftPayload['ai_taxonomy_raw_reply'] = $result['reply'];

            return $draftPayload;
        }

        $rationale = trim((string) ($parsed['rationale'] ?? ''));
        if ($rationale !== '') {
            $draftPayload['ai_taxonomy_rationale'] = $rationale;
        }
        $draftPayload['ai_taxonomy_source'] = 'openai_taxonomy_v1';

        if ($needCategory) {
            $slug = trim((string) ($parsed['primary_category_slug'] ?? ''));
            if ($slug !== '') {
                $catId = $categories->firstWhere('slug', $slug)?->id;
                if ($catId !== null) {
                    $draftPayload['primary_category_id'] = (int) $catId;
                } else {
                    $draftPayload['ai_taxonomy_unmatched_category_slug'] = $slug;
                }
            }
        }

        if ($needAudiences) {
            $slugs = $parsed['audience_slugs'] ?? [];
            if (! is_array($slugs)) {
                $slugs = [];
            }
            $ids = [];
            foreach ($slugs as $s) {
                if (! is_string($s)) {
                    continue;
                }
                $s = trim($s);
                if ($s === '') {
                    continue;
                }
                $aid = $audiences->firstWhere('slug', $s)?->id;
                if ($aid !== null) {
                    $ids[] = (int) $aid;
                }
            }
            $ids = array_values(array_unique($ids));
            if ($ids !== []) {
                $draftPayload['audience_ids'] = $ids;
            }
        }

        return $draftPayload;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @param  \Illuminate\Support\Collection<int, Audience>  $audiences
     */
    private function buildPrompt(string $brief, array $draftPayload, $categories, $audiences): string
    {
        $ctx = [
            'title' => $draftPayload['title'] ?? '',
            'short_description' => $draftPayload['short_description'] ?? '',
            'target_audience_text' => $draftPayload['target_audience_text'] ?? '',
        ];

        $catJson = $categories->map(fn (Category $c) => [
            'slug' => $c->slug,
            'name' => $c->name,
            'parent_id' => $c->parent_id,
        ])->values()->all();

        $audJson = $audiences->map(fn (Audience $a) => [
            'slug' => $a->slug,
            'name' => $a->name,
        ])->values()->all();

        $catList = json_encode($catJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $audList = json_encode($audJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $ctxJson = json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<TXT
Du ordnest einen Kurs der passenden Hauptkategorie und Zielgruppen zu. Nur Einträge aus den mitgelieferten Listen sind erlaubt (Slug exakt übernehmen).

Kursidee / Brief:
{$brief}

Entwurf-Kontext (JSON):
{$ctxJson}

Kategorien (JSON-Array, nur diese Slugs für primary_category_slug):
{$catList}

Zielgruppen (JSON-Array, nur diese Slugs für audience_slugs):
{$audList}

Antworte NUR mit einem JSON-Objekt (kein Markdown, kein Code-Fence):
{
  "primary_category_slug": string|null,
  "audience_slugs": string[],
  "rationale": string
}

Wenn keine Kategorie passt: primary_category_slug null. Wenn keine Zielgruppe passt: audience_slugs []. Begründung kurz auf Deutsch.
TXT;
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
