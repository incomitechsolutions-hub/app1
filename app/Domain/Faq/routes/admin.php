<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.faqs.')->group(function () {
    Route::get('faqs', fn () => abort(501))->name('index');
});
