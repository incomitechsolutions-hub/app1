<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Public;

use App\Domain\CourseCatalog\Models\Course;
use Illuminate\View\View;

class PublicCourseController
{
    public function show(string $slug): View
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->published()
            ->with([
                'primaryCategory',
                'difficultyLevel',
                'heroMedia',
                'categories',
                'tags',
                'discountTiers',
                'seoMeta.ogImageMedia',
            ])
            ->firstOrFail();

        return view('public.courses.show', compact('course'));
    }
}
