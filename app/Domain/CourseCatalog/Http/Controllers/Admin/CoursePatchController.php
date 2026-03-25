<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\PatchCourseFieldsRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Services\CourseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CoursePatchController extends Controller
{
    public function __construct(
        private readonly CourseService $courses
    ) {}

    public function __invoke(PatchCourseFieldsRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        if ($course->trashed()) {
            return response()->json(['message' => __('Kurs liegt im Papierkorb.')], 422);
        }

        try {
            $this->courses->patchFields($course, $request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => __('Validierung fehlgeschlagen.'), 'errors' => $e->errors()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
