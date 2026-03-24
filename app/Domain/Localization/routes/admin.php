<?php

use App\Domain\Localization\Http\Controllers\Admin\LocaleController;
use App\Domain\Localization\Http\Controllers\Admin\MarketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:localization'])->prefix('admin')->name('admin.localization.')->group(function () {
    Route::resource('markets', MarketController::class)->except(['show']);
    Route::resource('locales', LocaleController::class)->except(['show']);
});
