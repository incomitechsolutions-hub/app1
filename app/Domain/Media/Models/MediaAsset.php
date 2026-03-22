<?php

namespace App\Domain\Media\Models;

use App\Domain\CourseCatalog\Models\Course;
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
}
