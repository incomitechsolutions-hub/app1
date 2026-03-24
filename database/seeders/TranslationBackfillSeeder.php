<?php

namespace Database\Seeders;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseTranslation;
use App\Domain\Localization\Models\Locale;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\CategoryTranslation;
use Illuminate\Database\Seeder;

class TranslationBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::query()->where('code', 'de')->first();
        if ($locale === null) {
            return;
        }

        foreach (Category::query()->cursor() as $category) {
            CategoryTranslation::query()->updateOrCreate(
                [
                    'category_id' => $category->id,
                    'locale_id' => $locale->id,
                ],
                [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ]
            );
        }

        foreach (Course::query()->withTrashed()->cursor() as $course) {
            CourseTranslation::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'locale_id' => $locale->id,
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
}
