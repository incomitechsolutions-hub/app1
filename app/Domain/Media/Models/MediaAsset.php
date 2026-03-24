<?php

namespace App\Domain\Media\Models;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAsset extends Model
{
    protected $fillable = [
        'disk',
        'file_name',
        'file_path',
        'mime_type',
        'alt_text',
    ];

    public function coursesAsHero(): HasMany
    {
        return $this->hasMany(Course::class, 'hero_media_asset_id');
    }

    public function categoriesAsIcon(): HasMany
    {
        return $this->hasMany(Category::class, 'icon_media_asset_id');
    }

    public function categoriesAsHeader(): HasMany
    {
        return $this->hasMany(Category::class, 'header_media_asset_id');
    }
}
