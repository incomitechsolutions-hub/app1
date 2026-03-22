<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/sitemap.xml', fn () => abort(501))->name('public.sitemap');
});
