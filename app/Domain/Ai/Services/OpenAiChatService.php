<?php

namespace App\Domain\Ai\Services;

use Illuminate\Support\Facades\Http;

class OpenAiChatService
{
    /**
     * @return array{ok: bool, reply?: string, error?: string, status?: int}
     */
    public function sendChatMessage(
        string $apiKey,
        string $baseUrl,
        string $model,
        string $userMessage
    ): array {
        $url = rtrim($baseUrl, '/').'/chat/completions';

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(90)
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ]);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }

        if (! $response->successful()) {
            $body = $response->json();
            $msg = is_array($body) && isset($body['error']['message'])
                ? (string) $body['error']['message']
                : $response->body();

            return [
                'ok' => false,
                'error' => $msg !== '' ? $msg : 'HTTP '.$response->status(),
                'status' => $response->status(),
            ];
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;
        if (! is_string($text) || $text === '') {
            return [
                'ok' => false,
                'error' => 'Unerwartete Antwort der API.',
            ];
        }

        return [
            'ok' => true,
            'reply' => $text,
        ];
    }
}
