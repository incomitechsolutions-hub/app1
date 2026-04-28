<?php

namespace App\Services\Ai;

class AiProviderFactory
{
    public function make(): ?AiProviderInterface
    {
        $provider = strtolower((string) env('AI_PROVIDER', 'openai'));
        $hasApiKey = trim((string) env('OPENAI_API_KEY', '')) !== '';

        if ($provider === 'openai' && $hasApiKey) {
            return app(OpenAiProvider::class);
        }

        return null;
    }
}

