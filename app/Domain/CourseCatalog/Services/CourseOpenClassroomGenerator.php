<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseOpenClassroom;

class CourseOpenClassroomGenerator
{
    /** @var list<string> */
    private const LOCATIONS = [
        'Berlin', 'Hamburg', 'München', 'Köln', 'Stuttgart', 'Bremen', 'Erfurt', 'Bonn', 'Frankfurt', 'Dresden', 'Hannover', 'Nürnberg',
    ];

    public function generateForNewCourse(Course $course): void
    {
        $course->openClassrooms()->delete();

        $count = random_int(3, 5);
        $locations = self::LOCATIONS;
        shuffle($locations);

        $base = now()->addWeeks(2)->startOfDay()->setTime(9, 30);
        $durationHours = $course->duration_days !== null
            ? max(1, (float) $course->duration_days * 8)
            : 14.0;

        for ($i = 0; $i < $count; $i++) {
            $startsAt = (clone $base)->addWeeks($i * 2);
            $location = $locations[$i % count($locations)];

            CourseOpenClassroom::query()->create([
                'course_id' => $course->id,
                'starts_at' => $startsAt,
                'duration_hours' => $durationHours,
                'location_label' => $location,
                'sort_order' => $i,
            ]);
        }
    }
}
