<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:pages'])->prefix('admin')->name('admin.pages.')->group(function () {
    Route::get('pages', PlaceholderController::class)
        ->defaults('title', 'Seiten')
        ->name('index');
});
