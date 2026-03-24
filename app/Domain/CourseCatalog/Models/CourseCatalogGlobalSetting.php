<?php

namespace App\Domain\CourseCatalog\Models;

use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Domain\CourseCatalog\Enums\GroupDiscountLayout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseCatalogGlobalSetting extends Model
{
    protected $table = 'course_catalog_global_settings';

    protected $fillable = [
        'default_currency',
        'default_delivery_format',
        'default_language_code',
        'default_min_participants',
        'tax_rate_percent',
        'early_bird_enabled',
        'early_bird_days_before',
        'early_bird_discount_percent',
        'group_discount_enabled',
        'group_discount_layout',
    ];

    protected function casts(): array
    {
        return [
            'default_delivery_format' => DeliveryFormat::class,
            'default_min_participants' => 'integer',
            'tax_rate_percent' => 'decimal:2',
            'early_bird_enabled' => 'boolean',
            'early_bird_days_before' => 'integer',
            'early_bird_discount_percent' => 'decimal:2',
            'group_discount_enabled' => 'boolean',
            'group_discount_layout' => GroupDiscountLayout::class,
        ];
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'default_currency' => 'EUR',
                'default_delivery_format' => DeliveryFormat::Presence,
                'default_language_code' => 'de',
                'default_min_participants' => 3,
                'tax_rate_percent' => 19,
                'early_bird_enabled' => false,
                'early_bird_days_before' => 50,
                'early_bird_discount_percent' => 3,
                'group_discount_enabled' => false,
                'group_discount_layout' => GroupDiscountLayout::Layout2,
            ]
        );
    }

    public function groupDiscountTiers(): HasMany
    {
        return $this->hasMany(CourseGroupDiscountTier::class)->orderBy('sort_order');
    }
}
