<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.media.')->group(function () {
    Route::get('media', fn () => abort(501))->name('index');
});
