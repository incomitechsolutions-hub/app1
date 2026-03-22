<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/kategorie/{slug}', fn () => abort(501))->name('public.categories.show');
});
