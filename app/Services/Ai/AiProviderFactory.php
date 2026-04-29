<?php

namespace App\Services\Ai;

use App\Domain\Ai\Models\AiSetting;

class AiProviderFactory
{
    public function make(): ?AiProviderInterface
    {
        $provider = strtolower((string) env('AI_PROVIDER', 'openai'));
        $envHasApiKey = trim((string) env('OPENAI_API_KEY', '')) !== '';
        $settingsHasApiKey = false;
        try {
            $settingsHasApiKey = AiSetting::singleton()->hasOpenAiApiKey();
        } catch (\Throwable) {
            $settingsHasApiKey = false;
        }

        if ($provider === 'openai' && ($envHasApiKey || $settingsHasApiKey)) {
            return app(OpenAiProvider::class);
        }

        return null;
    }
}

