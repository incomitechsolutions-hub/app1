<?php

namespace App\Domain\Taxonomy\Models;

use App\Domain\CourseCatalog\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Audience extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_audiences');
    }
}
