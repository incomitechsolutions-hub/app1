<?php

namespace App\Domain\Seo\Http\Controllers\Public;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use Illuminate\Http\Response;

class SitemapController
{
    public function index(): Response
    {
        $urls = [];

        foreach (Course::query()->published()->cursor() as $course) {
            $seo = $course->seoMeta;
            if ($seo !== null && ! $seo->robots_index) {
                continue;
            }
            $urls[] = route('public.courses.show', ['slug' => $course->slug], true);
        }

        foreach (Category::query()->where('status', 'published')->cursor() as $category) {
            $seo = $category->seoMeta;
            if ($seo !== null && ! $seo->robots_index) {
                continue;
            }
            $urls[] = route('public.categories.show', ['slug' => $category->slug], true);
        }

        return response()
            ->view('public.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
