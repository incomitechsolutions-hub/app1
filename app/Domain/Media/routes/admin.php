<?php

use App\Domain\Media\Http\Controllers\Admin\MediaLibraryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:media'])->prefix('admin')->name('admin.media.')->group(function () {
    Route::get('media', [MediaLibraryController::class, 'index'])->name('index');
    Route::post('media', [MediaLibraryController::class, 'store'])->name('store');
});
