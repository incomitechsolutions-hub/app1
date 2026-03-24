<?php

use App\Http\Controllers\Admin\PlaceholderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'module.enabled:identity'])->prefix('admin')->name('admin.identity.')->group(function () {
    Route::get('users', PlaceholderController::class)
        ->defaults('title', 'Benutzer')
        ->name('users.index');
});
