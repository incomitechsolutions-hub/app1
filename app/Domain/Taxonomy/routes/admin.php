<?php

use App\Domain\Taxonomy\Http\Controllers\Admin\CategoryController;
use App\Domain\Taxonomy\Http\Controllers\Admin\CategoryImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:taxonomy'])->prefix('admin')->name('admin.taxonomy.')->group(function () {
    Route::get('categories/main', [CategoryController::class, 'index'])
        ->defaults('level', 'root')
        ->name('categories.main');

    Route::get('categories/sub', [CategoryController::class, 'index'])
        ->defaults('level', 'child')
        ->name('categories.sub');

    Route::get('categories/import', [CategoryImportController::class, 'show'])
        ->name('categories.import');
    Route::post('categories/import/preview', [CategoryImportController::class, 'preview'])
        ->name('categories.import.preview');
    Route::post('categories/import', [CategoryImportController::class, 'import'])
        ->name('categories.import.execute');

    Route::resource('categories', CategoryController::class)->except('show');
});
