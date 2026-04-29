<?php

namespace App\Services\Ai;

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

        return $this->chatJson($prompt);
    }

    /**
     * @return array<string, mixed>
     */
    private function chatJson(string $prompt): array
    {
        $apiKey = (string) env('OPENAI_API_KEY', '');
        if ($apiKey === '') {
            return [];
        }

        $baseUrl = rtrim((string) env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
        $model = (string) env('OPENAI_MODEL', 'gpt-4o-mini');

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

