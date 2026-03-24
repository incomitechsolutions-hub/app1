<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseGroupDiscountTier extends Model
{
    protected $table = 'course_group_discount_tiers';

    protected $fillable = [
        'course_catalog_global_setting_id',
        'sort_order',
        'min_participants',
        'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'min_participants' => 'integer',
            'discount_percent' => 'decimal:2',
        ];
    }

    public function globalSetting(): BelongsTo
    {
        return $this->belongsTo(CourseCatalogGlobalSetting::class, 'course_catalog_global_setting_id');
    }
}
