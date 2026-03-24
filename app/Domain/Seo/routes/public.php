<?php

use App\Domain\Seo\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');
});
