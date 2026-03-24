<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:locations'])->prefix('admin')->name('admin.locations.')->group(function () {
    Route::get('locations', PlaceholderController::class)
        ->defaults('title', 'Standorte')
        ->name('index');
});
