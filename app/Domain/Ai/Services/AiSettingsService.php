<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Models\AiSetting;

class AiSettingsService
{
    /**
     * @param  array{default_model: string, openai_base_url: string, openai_api_key?: string|null}  $data
     */
    public function update(AiSetting $settings, array $data): void
    {
        $settings->default_model = $data['default_model'];
        $settings->openai_base_url = $data['openai_base_url'];

        $key = $data['openai_api_key'] ?? null;
        if (is_string($key) && $key !== '') {
            $settings->openai_api_key = $key;
        }

        $settings->save();
    }
}
