<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.locations.')->group(function () {
    Route::get('locations', fn () => abort(501))->name('index');
});
