<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('/anfrage', fn () => abort(501))->name('public.inquiries.store');
});
