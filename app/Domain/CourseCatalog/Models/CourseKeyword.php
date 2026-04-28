<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseKeyword extends Model
{
    protected $fillable = [
        'course_id',
        'analysis_id',
        'keyword',
        'type',
        'intent',
        'source',
        'relevance_score',
        'commercial_score',
        'course_fit_score',
        'selected',
    ];

    protected function casts(): array
    {
        return [
            'source' => 'array',
            'selected' => 'boolean',
            'relevance_score' => 'integer',
            'commercial_score' => 'integer',
            'course_fit_score' => 'integer',
            'course_id' => 'integer',
            'analysis_id' => 'integer',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(CourseKeywordAnalysis::class, 'analysis_id');
    }
}

