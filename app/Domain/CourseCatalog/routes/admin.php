<?php

use App\Domain\CourseCatalog\Http\Controllers\Admin\AiCourseGenerationController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\AiCourseGenerationWizardController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\AiCourseWizardController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\KeywordResearchController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseCatalogSettingsController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseController;
use App\Domain\CourseCatalog\Http\Controllers\Admin\CourseCrawlController;
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

    Route::get('courses/ai-generator', fn () => redirect()->route('admin.course-catalog.courses.ai-generation.create'))
        ->name('courses.ai-generator');

    Route::get('courses/ai-generation', [AiCourseGenerationController::class, 'create'])
        ->name('courses.ai-generation.create');
    Route::post('courses/ai-generation', [AiCourseGenerationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('courses.ai-generation.store');
    Route::post('courses/ai-generation/keyword-research', KeywordResearchController::class)
        ->middleware('throttle:30,1')
        ->name('courses.ai-generation.keyword-research');
    Route::get('courses/ai-generation/{ai_course_generation_session}', [AiCourseGenerationController::class, 'show'])
        ->name('courses.ai-generation.show');

    Route::get('courses/ai-generation/{ai_course_generation_session}/wizard', [AiCourseGenerationWizardController::class, 'wizard'])
        ->name('courses.ai-generation.wizard');
    Route::patch('courses/ai-generation/{ai_course_generation_session}/draft', [AiCourseGenerationWizardController::class, 'updateDraft'])
        ->name('courses.ai-generation.draft.update');
    Route::post('courses/ai-generation/{ai_course_generation_session}/regenerate', [AiCourseGenerationWizardController::class, 'regenerate'])
        ->middleware('throttle:15,1')
        ->name('courses.ai-generation.regenerate');
    Route::post('courses/ai-generation/{ai_course_generation_session}/confirm-steps', [AiCourseGenerationWizardController::class, 'confirmSteps'])
        ->name('courses.ai-generation.confirm-steps');
    Route::post('courses/ai-generation/{ai_course_generation_session}/finalize', [AiCourseGenerationWizardController::class, 'finalize'])
        ->middleware('throttle:10,1')
        ->name('courses.ai-generation.finalize');

    Route::post('courses/crawl-from-website', [CourseCrawlController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('courses.crawl-from-website');

    Route::post('ai-course-wizard/keyword-discovery', [AiCourseWizardController::class, 'keywordDiscovery'])
        ->middleware('throttle:20,1')
        ->name('ai-wizard.keyword-discovery');
    Route::post('ai-course-wizard/save-selection', [AiCourseWizardController::class, 'saveSelection'])
        ->middleware('throttle:20,1')
        ->name('ai-wizard.save-selection');
    Route::post('ai-course-wizard/regenerate-field', [AiCourseWizardController::class, 'regenerateField'])
        ->middleware('throttle:30,1')
        ->name('ai-wizard.regenerate-field');

    Route::resource('programs', ProgramController::class);
    Route::resource('courses', CourseController::class);
});

