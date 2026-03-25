<?php

namespace App\Domain\Ai\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'ai_settings';

    protected $fillable = [
        'openai_api_key',
        'default_model',
        'openai_base_url',
    ];

    protected function casts(): array
    {
        return [
            'openai_api_key' => 'encrypted',
        ];
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'default_model' => 'gpt-4o-mini',
                'openai_base_url' => 'https://api.openai.com/v1',
            ]
        );
    }

    public function hasOpenAiApiKey(): bool
    {
        return $this->openai_api_key !== null && $this->openai_api_key !== '';
    }
}
