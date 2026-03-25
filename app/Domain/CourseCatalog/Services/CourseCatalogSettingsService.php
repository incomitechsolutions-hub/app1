<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Models\CourseCoupon;
use App\Domain\CourseCatalog\Models\CourseGroupDiscountTier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CourseCatalogSettingsService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSettings(array $data): CourseCatalogGlobalSetting
    {
        return DB::transaction(function () use ($data) {
            $settings = CourseCatalogGlobalSetting::singleton();

            $attrs = Arr::only($data, [
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
            ]);
            $settings->fill($attrs);
            $settings->save();

            return $settings->fresh(['groupDiscountTiers']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createGroupDiscountTier(array $data): CourseGroupDiscountTier
    {
        $settings = CourseCatalogGlobalSetting::singleton();
        $nextOrder = (int) $settings->groupDiscountTiers()->max('sort_order') + 1;

        return $settings->groupDiscountTiers()->create([
            'sort_order' => $nextOrder,
            'min_participants' => (int) $data['min_participants'],
            'discount_percent' => (float) $data['discount_percent'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateGroupDiscountTier(CourseGroupDiscountTier $tier, array $data): CourseGroupDiscountTier
    {
        $tier->update([
            'min_participants' => (int) $data['min_participants'],
            'discount_percent' => (float) $data['discount_percent'],
        ]);

        return $tier->fresh();
    }

    public function deleteGroupDiscountTier(CourseGroupDiscountTier $tier): void
    {
        $tier->delete();
    }

    public function assertTierBelongsToSingleton(CourseGroupDiscountTier $tier): void
    {
        $settings = CourseCatalogGlobalSetting::singleton();
        if ((int) $tier->course_catalog_global_setting_id !== (int) $settings->id) {
            throw new RuntimeException('Invalid group discount tier.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCoupon(array $data): CourseCoupon
    {
        return CourseCoupon::query()->create([
            'code' => strtoupper(trim((string) $data['code'])),
            'discount_percent' => $data['discount_percent'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function deleteCoupon(CourseCoupon $coupon): void
    {
        $coupon->delete();
    }
}
