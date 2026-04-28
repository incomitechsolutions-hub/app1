<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseKeywordAnalysis extends Model
{
    protected $fillable = [
        'course_id',
        'topic',
        'subtopics',
        'raw_google_response',
        'raw_ai_response',
        'selected_primary_keyword',
        'selected_keywords',
        'selected_clusters',
        'seo_opportunity_score',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtopics' => 'array',
            'raw_google_response' => 'array',
            'raw_ai_response' => 'array',
            'selected_keywords' => 'array',
            'selected_clusters' => 'array',
            'seo_opportunity_score' => 'integer',
            'created_by' => 'integer',
            'course_id' => 'integer',
        ];
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(CourseKeyword::class, 'analysis_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

