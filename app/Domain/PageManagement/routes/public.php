<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/thema/{slug}', fn () => abort(501))->name('public.topics.show');
});
