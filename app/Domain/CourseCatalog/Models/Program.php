<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Program extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'status',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'program_course')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
