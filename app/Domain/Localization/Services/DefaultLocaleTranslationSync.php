<?php

namespace App\Domain\Localization\Services;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseTranslation;
use App\Domain\Localization\Models\Locale;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\CategoryTranslation;

class DefaultLocaleTranslationSync
{
    private const DEFAULT_LOCALE_CODE = 'de';

    public function syncCategory(Category $category): void
    {
        $localeId = Locale::query()->where('code', self::DEFAULT_LOCALE_CODE)->value('id');
        if ($localeId === null) {
            return;
        }

        CategoryTranslation::query()->updateOrCreate(
            [
                'category_id' => $category->id,
                'locale_id' => $localeId,
            ],
            [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ]
        );
    }

    public function syncCourse(Course $course): void
    {
        $localeId = Locale::query()->where('code', self::DEFAULT_LOCALE_CODE)->value('id');
        if ($localeId === null) {
            return;
        }

        CourseTranslation::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'locale_id' => $localeId,
            ],
            [
                'title' => $course->title,
                'slug' => $course->slug,
                'short_description' => $course->short_description,
                'long_description' => $course->long_description,
            ]
        );
    }
}
