<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:taxonomy'])->prefix('admin')->name('admin.taxonomy.')->group(function () {
    Route::get('categories', PlaceholderController::class)
        ->defaults('title', 'Kategorien')
        ->name('categories.index');
});
