<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\StoreCourseRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\UpdateCourseRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Services\CourseService;
use App\Domain\Taxonomy\Models\Audience;
use App\Http\Controllers\Controller;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\DifficultyLevel;
use App\Domain\Taxonomy\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courses
    ) {
        $this->authorizeResource(Course::class, 'course');
    }

    public function index(): View
    {
        $courses = Course::query()
            ->with('primaryCategory')
            ->latest()
            ->paginate(20);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('admin.courses.create', $this->formOptions());
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = $this->courses->create($request->validated());

        return redirect()
            ->route('admin.course-catalog.courses.show', $course)
            ->with('status', __('Course created.'));
    }

    public function show(Course $course): View
    {
        $course->load([
            'primaryCategory',
            'difficultyLevel',
            'heroMedia',
            'categories',
            'tags',
            'audiences',
            'modules',
            'learningObjectives',
            'prerequisites',
        ]);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $course->load([
            'categories',
            'tags',
            'audiences',
            'modules',
            'learningObjectives',
            'prerequisites',
        ]);

        return view('admin.courses.edit', array_merge(
            ['course' => $course],
            $this->formOptions()
        ));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->courses->update($course, $request->validated());

        return redirect()
            ->route('admin.course-catalog.courses.show', $course)
            ->with('status', __('Course updated.'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->courses->delete($course);

        return redirect()
            ->route('admin.course-catalog.courses.index')
            ->with('status', __('Course removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'difficultyLevels' => DifficultyLevel::query()->orderBy('sort_order')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'audiences' => Audience::query()->orderBy('name')->get(),
        ];
    }
}
