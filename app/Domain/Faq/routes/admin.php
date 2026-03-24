<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:faqs'])->prefix('admin')->name('admin.faqs.')->group(function () {
    Route::get('faqs', PlaceholderController::class)
        ->defaults('title', 'FAQs')
        ->name('index');
});
