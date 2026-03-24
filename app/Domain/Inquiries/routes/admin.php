<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:inquiries'])->prefix('admin')->name('admin.inquiries.')->group(function () {
    Route::get('inquiries', PlaceholderController::class)
        ->defaults('title', 'Anfragen')
        ->name('index');
});
