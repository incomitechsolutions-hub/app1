<?php

namespace App\Domain\PromptManagement\Models;

use App\Domain\PromptManagement\Enums\PromptUseCase;
use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $table = 'ai_prompts';

    protected $fillable = [
        'title',
        'slug',
        'use_case',
        'body',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'use_case' => PromptUseCase::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
