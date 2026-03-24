<?php

use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseCatalogSettingsController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:course_catalog'])->prefix('admin')->name('admin.course-catalog.')->group(function () {
    Route::get('course-catalog/settings', [CourseCatalogSettingsController::class, 'edit'])
        ->name('settings.edit');
    Route::put('course-catalog/settings', [CourseCatalogSettingsController::class, 'update'])
        ->name('settings.update');
    Route::post('course-catalog/settings/coupons', [CourseCatalogSettingsController::class, 'storeCoupon'])
        ->name('settings.coupons.store');
    Route::delete('course-catalog/settings/coupons/{coupon}', [CourseCatalogSettingsController::class, 'destroyCoupon'])
        ->name('settings.coupons.destroy');

    Route::post('courses/{course}/restore', [CourseController::class, 'restore'])
        ->name('courses.restore');
    Route::resource('courses', CourseController::class);
});
