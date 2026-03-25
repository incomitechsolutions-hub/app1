<?php

use App\Domain\Ai\Http\Controllers\Admin\AiSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:ai'])->prefix('admin')->name('admin.ai.')->group(function () {
    Route::get('ai/settings', [AiSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('ai/settings', [AiSettingsController::class, 'update'])->name('settings.update');
    Route::post('ai/settings/test', [AiSettingsController::class, 'test'])
        ->middleware('throttle:20,1')
        ->name('settings.test');
});
