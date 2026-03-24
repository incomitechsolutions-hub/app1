<?php

use App\Domain\Taxonomy\Http\Controllers\Public\PublicCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/kategorie/{slug}', [PublicCategoryController::class, 'show'])->name('public.categories.show');
});
