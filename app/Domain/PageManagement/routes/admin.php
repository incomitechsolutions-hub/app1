<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.pages.')->group(function () {
    Route::get('pages', fn () => abort(501))->name('index');
});
