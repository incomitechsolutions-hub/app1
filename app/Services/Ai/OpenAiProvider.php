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

        $content = (string) data_get($response->json(), 'choices.0.message.content', '');
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

        return $decoded + ['_meta' => ['reason' => null]];
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

