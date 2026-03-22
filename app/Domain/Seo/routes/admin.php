<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.seo.')->group(function () {
    Route::get('seo', PlaceholderController::class)
        ->defaults('title', 'SEO')
        ->name('index');
    Route::get('redirects', PlaceholderController::class)
        ->defaults('title', 'Weiterleitungen')
        ->name('redirects.index');
});
