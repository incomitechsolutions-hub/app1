<?php

namespace App\Services\Ai;

use App\Domain\Ai\Models\AiSetting;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProviderInterface
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateKeywordCluster(array $context): array
    {
        $prompt = "Gib valides JSON zurück mit keys keywords[], clusters[], suggested_seo{}.\n".
            'Kontext: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->chatJson($prompt);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateCourseContent(array $context): array
    {
        $prompt = "Gib valides JSON mit keys seo{}, base{}, details{} zurück.\n".
            'Kontext: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($context['prompt_text'] ?? null) && trim((string) $context['prompt_text']) !== '') {
            $prompt = "Zusatzanweisung fuer Regenerate:\n".trim((string) $context['prompt_text'])."\n\n".$prompt;
        }

        return $this->chatJson($prompt);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function regenerateField(array $context): array
    {
        $fieldName = (string) ($context['field_name'] ?? data_get($context, 'current_context.field_name', ''));
        $fieldPath = (string) ($context['field_path'] ?? data_get($context, 'current_context.field_path', ''));
        $prompt = "Gib valides JSON mit key value zurück.\n".
            "Zielfeld: {$fieldName} ({$fieldPath})\n".
            'Kontext: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($context['prompt_text'] ?? null) && trim((string) $context['prompt_text']) !== '') {
            $prompt = "Zusatzanweisung fuer Regenerate:\n".trim((string) $context['prompt_text'])."\n\n".$prompt;
        }

        return $this->chatJsonForFieldRegenerate($prompt);
    }

    /**
     * @return array<string, mixed>
     */
    private function chatJsonForFieldRegenerate(string $prompt): array
    {
        $apiKey = trim((string) env('OPENAI_API_KEY', ''));
        $baseUrl = rtrim((string) env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
        $model = (string) env('OPENAI_MODEL', 'gpt-4o-mini');

        if ($apiKey === '') {
            try {
                $settings = AiSetting::singleton();
                $apiKey = trim((string) ($settings->openai_api_key ?? ''));
                $baseUrl = rtrim((string) ($settings->openai_base_url ?: 'https://api.openai.com/v1'), '/');
                $model = (string) ($settings->default_model ?: 'gpt-4o-mini');
            } catch (\Throwable) {
                // Keep defaults and fail below if key is still empty.
            }
        }

        if ($apiKey === '') {
            return ['_meta' => ['reason' => 'missing_api_key']];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(60)
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Antworte ausschließlich mit JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                ]);
        } catch (\Throwable) {
            return ['_meta' => ['reason' => 'request_failed']];
        }

        if (! $response->successful()) {
            return ['_meta' => ['reason' => 'http_error', 'status' => $response->status()]];
        }

        $rawJson = $response->json();
        $content = (string) data_get($rawJson, 'choices.0.message.content', '');
        if ($content === '') {
            return ['_meta' => ['reason' => 'empty_content']];
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            // Try to recover if model returned extra text/markdown around JSON.
            if (preg_match('/\{[\s\S]*\}/', $content, $match) === 1) {
                $decoded = json_decode((string) $match[0], true);
            }
        }

        if (! is_array($decoded)) {
            return ['_meta' => ['reason' => 'json_decode_failed']];
        }

        $value = $this->extractFieldValue($decoded, $content, $rawJson);
        if ($value === null) {
            return $decoded + ['_meta' => ['reason' => 'empty_value']];
        }

        $decoded['value'] = $value;

        return $decoded + ['_meta' => ['reason' => null]];
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @param  array<string, mixed>  $rawJson
     */
    private function extractFieldValue(array $decoded, string $content, array $rawJson): ?string
    {
        $candidates = [
            $decoded['value'] ?? null,
            $decoded['title'] ?? null,
            $decoded['subtitle'] ?? null,
            $decoded['text'] ?? null,
            $decoded['output_text'] ?? null,
            $decoded['short_description'] ?? null,
            $decoded['long_description'] ?? null,
            $decoded['meta_description'] ?? null,
            $decoded['seo_title'] ?? null,
            $decoded['focus_keyword'] ?? null,
            $decoded['tags_csv'] ?? null,
            $decoded['target_audience_text'] ?? null,
            $decoded['prerequisites_text'] ?? null,
            $decoded['description'] ?? null,
            $decoded['content'] ?? null,
            data_get($decoded, 'data.value'),
            data_get($decoded, 'result.value'),
            data_get($rawJson, 'choices.0.message.content'),
        ];

        foreach ($candidates as $candidate) {
            $resolved = $this->normalizeFieldValue($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        // If response is an object, pick the first readable string value as a last structured fallback.
        foreach ($decoded as $value) {
            $resolved = $this->normalizeFieldValue($value);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        // Last resort: accept plain text responses by stripping code fences.
        $plain = trim(preg_replace('/^```(?:json)?|```$/m', '', $content) ?? '');
        if ($plain !== '' && ! str_starts_with($plain, '{') && ! str_starts_with($plain, '[')) {
            return $plain;
        }

        return null;
    }

    private function normalizeFieldValue(mixed $candidate): ?string
    {
        if (is_string($candidate)) {
            $trimmed = trim($candidate);
            if ($trimmed === '') {
                return null;
            }

            // If candidate itself is JSON, try to extract inner value-like keys.
            if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                $json = json_decode($trimmed, true);
                if (is_array($json)) {
                    foreach ([
                        'value',
                        'title',
                        'subtitle',
                        'text',
                        'output_text',
                        'short_description',
                        'long_description',
                        'meta_description',
                        'seo_title',
                        'focus_keyword',
                        'tags_csv',
                        'target_audience_text',
                        'prerequisites_text',
                        'description',
                        'content',
                    ] as $key) {
                        if (isset($json[$key]) && is_string($json[$key]) && trim($json[$key]) !== '') {
                            return trim($json[$key]);
                        }
                    }

                    // Prevent raw JSON blobs from being inserted into text fields.
                    return null;
                }
            }

            return $trimmed;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function chatJson(string $prompt): array
    {
        $apiKey = trim((string) env('OPENAI_API_KEY', ''));
        $baseUrl = rtrim((string) env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
        $model = (string) env('OPENAI_MODEL', 'gpt-4o-mini');

        // Prefer .env for local/dev overrides, but fall back to stored admin AI settings.
        if ($apiKey === '') {
            try {
                $settings = AiSetting::singleton();
                $apiKey = trim((string) ($settings->openai_api_key ?? ''));
                $baseUrl = rtrim((string) ($settings->openai_base_url ?: 'https://api.openai.com/v1'), '/');
                $model = (string) ($settings->default_model ?: 'gpt-4o-mini');
            } catch (\Throwable) {
                // Keep defaults; missing settings should gracefully fall through.
            }
        }

        if ($apiKey === '') {
            return [];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(60)
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Antworte ausschließlich mit JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                ]);
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '');
        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }
}

