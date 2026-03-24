<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Models\CourseCoupon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

            $this->syncGroupDiscountTiers($settings, $data['group_discount_tiers'] ?? []);

            return $settings->fresh(['groupDiscountTiers']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncGroupDiscountTiers(CourseCatalogGlobalSetting $settings, array $rows): void
    {
        $settings->groupDiscountTiers()->delete();
        $rows = array_values(array_filter($rows, fn ($row) => isset($row['min_participants']) && $row['min_participants'] !== '' && $row['min_participants'] !== null));
        foreach ($rows as $index => $row) {
            $settings->groupDiscountTiers()->create([
                'sort_order' => $index,
                'min_participants' => max(1, (int) $row['min_participants']),
                'discount_percent' => (float) ($row['discount_percent'] ?? 0),
            ]);
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
