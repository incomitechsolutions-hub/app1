<?php

namespace App\Domain\Taxonomy\Models;

use App\Domain\CourseCatalog\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifficultyLevel extends Model
{
    protected $fillable = [
        'code',
        'label',
        'sort_order',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
