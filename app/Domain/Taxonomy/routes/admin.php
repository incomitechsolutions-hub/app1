<?php

use App\Domain\Taxonomy\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:taxonomy'])->prefix('admin')->name('admin.taxonomy.')->group(function () {
    Route::get('categories/main', [CategoryController::class, 'index'])
        ->defaults('level', 'root')
        ->name('categories.main');

    Route::get('categories/sub', [CategoryController::class, 'index'])
        ->defaults('level', 'child')
        ->name('categories.sub');

    Route::resource('categories', CategoryController::class)->except('show');
});
