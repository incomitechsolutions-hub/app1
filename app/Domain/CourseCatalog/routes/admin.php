<?php

use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseCatalogSettingsController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\CoursePatchController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\ProgramController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:course_catalog'])->prefix('admin')->name('admin.course-catalog.')->group(function () {
    Route::get('course-catalog/settings', [CourseCatalogSettingsController::class, 'edit'])
        ->name('settings.edit');
    Route::put('course-catalog/settings', [CourseCatalogSettingsController::class, 'update'])
        ->name('settings.update');
    Route::post('course-catalog/settings/group-discount-tiers', [CourseCatalogSettingsController::class, 'storeGroupDiscountTier'])
        ->name('settings.group-discount-tiers.store');
    Route::patch('course-catalog/settings/group-discount-tiers/{tier}', [CourseCatalogSettingsController::class, 'updateGroupDiscountTier'])
        ->name('settings.group-discount-tiers.update');
    Route::delete('course-catalog/settings/group-discount-tiers/{tier}', [CourseCatalogSettingsController::class, 'destroyGroupDiscountTier'])
        ->name('settings.group-discount-tiers.destroy');
    Route::post('course-catalog/settings/coupons', [CourseCatalogSettingsController::class, 'storeCoupon'])
        ->name('settings.coupons.store');
    Route::delete('course-catalog/settings/coupons/{coupon}', [CourseCatalogSettingsController::class, 'destroyCoupon'])
        ->name('settings.coupons.destroy');

    Route::post('courses/{course}/restore', [CourseController::class, 'restore'])
        ->name('courses.restore');
    Route::patch('courses/{course}/fields', CoursePatchController::class)
        ->name('courses.patch-fields');
    Route::resource('programs', ProgramController::class);
    Route::resource('courses', CourseController::class);
});
