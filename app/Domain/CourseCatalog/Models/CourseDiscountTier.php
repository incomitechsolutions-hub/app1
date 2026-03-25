<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseDiscountTier extends Model
{
    protected $fillable = [
        'course_id',
        'sort_order',
        'min_participants',
        'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'min_participants' => 'integer',
            'discount_percent' => 'decimal:2',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
