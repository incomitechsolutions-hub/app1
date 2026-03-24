<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\StoreCourseCouponRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\UpdateCourseCatalogSettingsRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseCoupon;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Services\CourseCatalogSettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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
        $coupons = CourseCoupon::query()->orderBy('code')->get();

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

    public function storeCoupon(StoreCourseCouponRequest $request): RedirectResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $this->settings->createCoupon($request->validated());

        return redirect()
            ->route('admin.course-catalog.settings.edit')
            ->with('status', __('Gutschein wurde angelegt.'));
    }

    public function destroyCoupon(CourseCoupon $coupon): RedirectResponse
    {
        $this->authorize('manageCatalogSettings', Course::class);

        $this->settings->deleteCoupon($coupon);

        return redirect()
            ->route('admin.course-catalog.settings.edit')
            ->with('status', __('Gutschein wurde gelöscht.'));
    }
}
