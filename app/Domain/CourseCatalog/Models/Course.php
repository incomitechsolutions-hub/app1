<?php

namespace App\Domain\CourseCatalog\Models;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Domain\Media\Models\MediaAsset;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\DifficultyLevel;
use App\Domain\Seo\Models\SeoMeta;
use App\Domain\Taxonomy\Models\Tag;
use App\Domain\Faq\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'subtitle',
        'slug',
        'external_course_code',
        'short_description',
        'long_description',
        'is_s2_modules_enabled',
        'target_audience_text',
        'prerequisites_text',
        'duration_hours',
        'language_code',
        'currency_code',
        'status',
        'primary_category_id',
        'difficulty_level_id',
        'hero_media_asset_id',
        'published_at',
        'author_name',
        'content_version',
        'price',
        'delivery_format',
        'delivery_formats',
        'lessons_count',
        'min_participants',
        'instructor_name',
        'certificate_label',
        'is_featured',
        'booking_url',
        'offer_url',
        'ai_prompt_source',
        'internal_notes',
        'average_rating',
        'ratings_count',
        'media_icon_enabled',
        'media_header_enabled',
        'media_video_enabled',
        'media_gallery_enabled',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'published_at' => 'datetime',
            'duration_hours' => 'decimal:2',
            'delivery_format' => DeliveryFormat::class,
            'is_featured' => 'boolean',
            'is_s2_modules_enabled' => 'boolean',
            'price' => 'decimal:2',
            'delivery_formats' => 'array',
            'average_rating' => 'decimal:2',
            'ratings_count' => 'integer',
            'lessons_count' => 'integer',
            'min_participants' => 'integer',
            'media_icon_enabled' => 'boolean',
            'media_header_enabled' => 'boolean',
            'media_video_enabled' => 'boolean',
            'media_gallery_enabled' => 'boolean',
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

    public function discountTiers(): HasMany
    {
        return $this->hasMany(CourseDiscountTier::class)->orderBy('sort_order');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'owner')->orderBy('sort_order');
    }

    public function openClassrooms(): HasMany
    {
        return $this->hasMany(CourseOpenClassroom::class)->orderBy('sort_order')->orderBy('starts_at');
    }

    public function courseRelations(): HasMany
    {
        return $this->hasMany(CourseRelation::class, 'course_id')->orderBy('sort_order');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_course')->withPivot('sort_order');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CourseTranslation::class);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'owner');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', CourseStatus::Published)
            ->whereNotNull('published_at');
    }
}
