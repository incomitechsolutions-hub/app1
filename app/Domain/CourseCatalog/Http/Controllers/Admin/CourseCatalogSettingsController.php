<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\StoreCourseCouponRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\StoreGroupDiscountTierRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\UpdateCourseCatalogSettingsRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\UpdateGroupDiscountTierRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseCoupon;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Models\CourseGroupDiscountTier;
use App\Domain\CourseCatalog\Services\CourseCatalogSettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CourseCatalogSettingsController extends Controller
{
    public function __construct(
        private readonly CourseCatalogSettingsService $settings
    ) {}

    public function edit(): View
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $settings = CourseCatalogGlobalSetting::singleton()->load('groupDiscountTiers');
        $coupons = collect();
        if (Schema::hasTable('course_coupons')) {
            $coupons = CourseCoupon::query()->orderBy('code')->get();
        }

        return view('admin.courses.settings', [
            'settings' => $settings,
            'coupons' => $coupons,
        ]);
    }

    public function update(UpdateCourseCatalogSettingsRequest $request): RedirectResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $this->settings->updateSettings($request->validated());

        return redirect()
            ->route('admin.course-catalog.settings.edit')
            ->with('status', __('Einstellungen wurden gespeichert.'));
    }

    public function storeGroupDiscountTier(StoreGroupDiscountTierRequest $request): JsonResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $tier = $this->settings->createGroupDiscountTier($request->validated());

        return response()->json([
            'tier' => [
                'id' => $tier->id,
                'min_participants' => $tier->min_participants,
                'discount_percent' => (string) $tier->discount_percent,
                'sort_order' => $tier->sort_order,
            ],
        ]);
    }

    public function updateGroupDiscountTier(UpdateGroupDiscountTierRequest $request, CourseGroupDiscountTier $tier): JsonResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $this->settings->assertTierBelongsToSingleton($tier);
        $tier = $this->settings->updateGroupDiscountTier($tier, $request->validated());

        return response()->json([
            'tier' => [
                'id' => $tier->id,
                'min_participants' => $tier->min_participants,
                'discount_percent' => (string) $tier->discount_percent,
                'sort_order' => $tier->sort_order,
            ],
        ]);
    }

    public function destroyGroupDiscountTier(CourseGroupDiscountTier $tier): JsonResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $this->settings->assertTierBelongsToSingleton($tier);
        $this->settings->deleteGroupDiscountTier($tier);

        return response()->json(['ok' => true]);
    }

    public function storeCoupon(StoreCourseCouponRequest $request): RedirectResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        if (! Schema::hasTable('course_coupons')) {
            return redirect()
                ->route('admin.course-catalog.settings.edit')
                ->withErrors(['code' => __('Bitte zuerst Datenbank-Migrationen ausführen (course_coupons fehlt).')]);
        }

        $this->settings->createCoupon($request->validated());

        return redirect()
            ->route('admin.course-catalog.settings.edit')
            ->with('status', __('Gutschein wurde angelegt.'));
    }

    public function destroyCoupon(CourseCoupon $coupon): RedirectResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        if (! Schema::hasTable('course_coupons')) {
            return redirect()
                ->route('admin.course-catalog.settings.edit')
                ->withErrors(['code' => __('Bitte zuerst Datenbank-Migrationen ausführen.')]);
        }

        $this->settings->deleteCoupon($coupon);

        return redirect()
            ->route('admin.course-catalog.settings.edit')
            ->with('status', __('Gutschein wurde gelöscht.'));
    }
}
