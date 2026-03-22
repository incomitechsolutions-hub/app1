<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/kurse/{slug}', fn () => abort(501))->name('public.courses.show');
    Route::get('/kurse/{courseSlug}/{locationSlug}', fn () => abort(501))->name('public.courses.showAtLocation');
});
