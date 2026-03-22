<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/standorte/{slug}', fn () => abort(501))->name('public.locations.show');
});
