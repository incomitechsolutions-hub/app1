<?php

namespace Tests\Feature\Admin;

use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Domain\CourseCatalog\Enums\GroupDiscountLayout;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCatalogSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_course_catalog_settings(): void
    {
        $this->get(route('admin.course-catalog.settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_and_update_course_catalog_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.course-catalog.settings.edit'))
            ->assertOk()
            ->assertSee('Kurs-Einstellungen');

        $this->actingAs($user)
            ->put(route('admin.course-catalog.settings.update'), [
                'default_currency' => 'EUR',
                'default_delivery_format' => DeliveryFormat::Online->value,
                'default_language_code' => 'de',
                'default_min_participants' => 2,
                'tax_rate_percent' => 19,
                'early_bird_enabled' => true,
                'early_bird_days_before' => 14,
                'early_bird_discount_percent' => 5,
                'group_discount_enabled' => true,
                'group_discount_layout' => GroupDiscountLayout::Layout1->value,
            ])
            ->assertRedirect(route('admin.course-catalog.settings.edit'));

        $settings = CourseCatalogGlobalSetting::singleton()->fresh(['groupDiscountTiers']);
        $this->assertSame('EUR', $settings->default_currency);
        $this->assertTrue($settings->early_bird_enabled);

        $this->actingAs($user)
            ->postJson(route('admin.course-catalog.settings.group-discount-tiers.store'), [
                'min_participants' => 2,
                'discount_percent' => 10,
            ])
            ->assertOk()
            ->assertJsonStructure(['tier' => ['id', 'min_participants', 'discount_percent', 'sort_order']]);

        $this->actingAs($user)
            ->postJson(route('admin.course-catalog.settings.group-discount-tiers.store'), [
                'min_participants' => 5,
                'discount_percent' => 15,
            ])
            ->assertOk();

        $settings = CourseCatalogGlobalSetting::singleton()->fresh(['groupDiscountTiers']);
        $this->assertCount(2, $settings->groupDiscountTiers);
    }

    public function test_authenticated_user_can_create_and_delete_coupon(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.course-catalog.settings.coupons.store'), [
                'code' => 'SAVE10',
                'discount_percent' => 10,
                'is_active' => true,
                'notes' => 'Test',
            ])
            ->assertRedirect(route('admin.course-catalog.settings.edit'));

        $this->assertDatabaseHas('course_coupons', [
            'code' => 'SAVE10',
            'discount_percent' => 10,
        ]);

        $coupon = \App\Domain\CourseCatalog\Models\CourseCoupon::query()->where('code', 'SAVE10')->firstOrFail();

        $this->actingAs($user)
            ->delete(route('admin.course-catalog.settings.coupons.destroy', $coupon))
            ->assertRedirect(route('admin.course-catalog.settings.edit'));

        $this->assertDatabaseMissing('course_coupons', ['id' => $coupon->id]);
    }
}
