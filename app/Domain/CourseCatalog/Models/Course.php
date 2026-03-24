<?php

namespace App\Domain\CourseCatalog\Models;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\Media\Models\MediaAsset;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\DifficultyLevel;
use App\Domain\Taxonomy\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'long_description',
        'duration_hours',
        'language_code',
        'status',
        'primary_category_id',
        'difficulty_level_id',
        'hero_media_asset_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'published_at' => 'datetime',
            'duration_hours' => 'decimal:2',
        ];
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function difficultyLevel(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class);
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'hero_media_asset_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'course_categories');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'course_tags');
    }

    public function audiences(): BelongsToMany
    {
        return $this->belongsToMany(Audience::class, 'course_audiences');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function learningObjectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class)->orderBy('sort_order');
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(Prerequisite::class)->orderBy('sort_order');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CourseTranslation::class);
    }
}
