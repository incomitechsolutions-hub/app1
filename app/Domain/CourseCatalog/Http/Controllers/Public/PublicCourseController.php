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
                'tags',
                'discountTiers',
                'modules',
                'faqs',
                'openClassrooms',
                'courseRelations.relatedCourse',
                'programs',
                'seoMeta.ogImageMedia',
            ])
            ->firstOrFail();

        return view('public.courses.show', compact('course'));
    }
}
