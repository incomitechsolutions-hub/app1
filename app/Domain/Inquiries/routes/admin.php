<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.inquiries.')->group(function () {
    Route::get('inquiries', fn () => abort(501))->name('index');
});
