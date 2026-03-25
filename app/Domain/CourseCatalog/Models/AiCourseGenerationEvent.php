<?php

namespace App\Domain\CourseCatalog\Models;

use App\Domain\CourseCatalog\Enums\AiCourseGenerationEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCourseGenerationEvent extends Model
{
    protected $fillable = [
        'ai_course_generation_session_id',
        'user_id',
        'type',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => AiCourseGenerationEventType::class,
            'meta' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiCourseGenerationSession::class, 'ai_course_generation_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
