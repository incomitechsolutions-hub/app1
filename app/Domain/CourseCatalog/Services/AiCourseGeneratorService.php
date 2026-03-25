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
        private readonly OpenAiChatService $openAi
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
            if ($p !== null && $p->use_case === PromptUseCase::CourseCreation) {
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
