<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseModule extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'duration_hours',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_hours' => 'decimal:2',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
