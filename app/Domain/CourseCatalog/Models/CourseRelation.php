<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRelation extends Model
{
    protected $fillable = [
        'course_id',
        'related_course_id',
        'relation_type',
        'sort_order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function relatedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'related_course_id');
    }
}
