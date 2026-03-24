<?php

namespace App\Domain\CourseCatalog\Models;

use App\Domain\Localization\Models\Locale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseTranslation extends Model
{
    protected $fillable = [
        'course_id',
        'locale_id',
        'title',
        'slug',
        'short_description',
        'long_description',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}
