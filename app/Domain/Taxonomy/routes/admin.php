<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.taxonomy.')->group(function () {
    Route::get('categories', fn () => abort(501))->name('categories.index');
});
