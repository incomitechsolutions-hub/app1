<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:media'])->prefix('admin')->name('admin.media.')->group(function () {
    Route::get('media', PlaceholderController::class)
        ->defaults('title', 'Medien')
        ->name('index');
});
