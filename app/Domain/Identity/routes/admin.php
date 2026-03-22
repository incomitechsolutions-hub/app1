<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.identity.')->group(function () {
    Route::get('users', fn () => abort(501))->name('users.index');
});
