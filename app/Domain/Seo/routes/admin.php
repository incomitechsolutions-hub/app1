<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.seo.')->group(function () {
    Route::get('seo', fn () => abort(501))->name('index');
    Route::get('redirects', fn () => abort(501))->name('redirects.index');
});
