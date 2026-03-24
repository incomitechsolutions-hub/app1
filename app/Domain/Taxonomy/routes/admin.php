<?php

use App\Domain\Taxonomy\Http\Controllers\Admin\CategoryController;
use App\Domain\Taxonomy\Http\Controllers\Admin\CategoryImportController;
use App\Domain\Taxonomy\Http\Controllers\Admin\CategoryTaxonomySettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:taxonomy'])->prefix('admin')->name('admin.taxonomy.')->group(function () {
    Route::get('categories/main', function () {
        return redirect()->route('admin.taxonomy.categories.index', ['level' => 'root']);
    })->name('categories.main');

    Route::get('categories/sub', function () {
        return redirect()->route('admin.taxonomy.categories.index', ['level' => 'child']);
    })->name('categories.sub');

    Route::get('categories/import', [CategoryImportController::class, 'show'])
        ->name('categories.import');
    Route::post('categories/import/preview', [CategoryImportController::class, 'preview'])
        ->name('categories.import.preview');
    Route::post('categories/import', [CategoryImportController::class, 'import'])
        ->name('categories.import.execute');

    Route::post('categories/bulk-update', [CategoryController::class, 'bulkUpdate'])
        ->name('categories.bulk-update');

    Route::get('category-taxonomy-settings', [CategoryTaxonomySettingsController::class, 'edit'])
        ->name('category-taxonomy-settings.edit');
    Route::put('category-taxonomy-settings', [CategoryTaxonomySettingsController::class, 'update'])
        ->name('category-taxonomy-settings.update');

    Route::resource('categories', CategoryController::class)->except('show');
});
