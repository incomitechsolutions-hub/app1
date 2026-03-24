<?php

namespace App\Domain\Localization\Models;

use App\Domain\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Market extends Model
{
    protected $fillable = [
        'country_code',
        'display_code',
        'name',
        'domain',
        'vat_rate',
        'is_active',
        'default_locale_id',
        'flag_media_asset_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function defaultLocale(): BelongsTo
    {
        return $this->belongsTo(Locale::class, 'default_locale_id');
    }

    public function flagMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'flag_media_asset_id');
    }
}
