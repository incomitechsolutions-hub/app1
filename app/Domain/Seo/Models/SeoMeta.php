<?php

namespace App\Domain\Seo\Models;

use App\Domain\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'seo_title',
        'meta_description',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'og_title',
        'og_description',
        'og_image_media_asset_id',
        'schema_json',
    ];

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'schema_json' => 'array',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'og_image_media_asset_id');
    }
}
