<?php

namespace App\Domain\CourseCatalog\Models;

use App\Domain\CourseCatalog\Enums\AiCourseGenerationSessionStatus;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiCourseGenerationSession extends Model
{
    protected $fillable = [
        'user_id',
        'ai_prompt_id',
        'status',
        'template_snapshot',
        'placeholder_input',
        'brief',
        'interpolated_body',
        'compiled_prompt',
        'full_prompt_audit',
        'draft_payload',
        'confirmed_steps',
        'last_regenerated_section',
        'resulting_course_id',
        'last_error',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'template_snapshot' => 'array',
            'placeholder_input' => 'array',
            'draft_payload' => 'array',
            'confirmed_steps' => 'array',
            'status' => AiCourseGenerationSessionStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiPrompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class, 'ai_prompt_id');
    }

    public function resultingCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'resulting_course_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AiCourseGenerationEvent::class, 'ai_course_generation_session_id')->orderBy('id');
    }
}
