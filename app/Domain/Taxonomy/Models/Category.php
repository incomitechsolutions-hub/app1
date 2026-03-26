<?php

namespace App\Domain\Taxonomy\Models;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Media\Models\MediaAsset;
use App\Domain\Seo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Category extends Model
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'status',
        'icon_media_asset_id',
        'header_media_asset_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'primary_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'icon_media_asset_id');
    }

    public function headerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'header_media_asset_id');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'owner');
    }
}
