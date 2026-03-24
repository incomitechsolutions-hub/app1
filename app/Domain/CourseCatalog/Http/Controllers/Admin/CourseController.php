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
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courses
    ) {
        $this->authorizeResource(Course::class, 'course');
    }

    public function index(Request $request): View
    {
        $trashed = $request->boolean('trashed');

        $query = Course::query()
            ->with('primaryCategory')
            ->latest();

        if ($trashed) {
            $query->onlyTrashed();
        }

        $courses = $query->paginate(20)->withQueryString();

        return view('admin.courses.index', compact('courses', 'trashed'));
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

    public function edit(Course $course): View|RedirectResponse
    {
        if ($course->trashed()) {
            return redirect()
                ->route('admin.course-catalog.courses.show', $course)
                ->with('status', __('Bitte zuerst wiederherstellen, dann bearbeiten.'));
        }

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
        if ($course->trashed()) {
            return redirect()
                ->route('admin.course-catalog.courses.show', $course)
                ->with('status', __('Bitte zuerst wiederherstellen, dann bearbeiten.'));
        }

        $this->courses->update($course, $request->validated());

        return redirect()
            ->route('admin.course-catalog.courses.show', $course)
            ->with('status', __('Course updated.'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->trashed()) {
            return redirect()
                ->route('admin.course-catalog.courses.index', ['trashed' => true])
                ->with('status', __('Der Kurs ist bereits im Papierkorb.'));
        }

        $this->courses->delete($course);

        return redirect()
            ->route('admin.course-catalog.courses.index')
            ->with('status', __('Course removed.'));
    }

    public function restore(Course $course): RedirectResponse
    {
        $this->authorize('restore', $course);

        if (! $course->trashed()) {
            return redirect()
                ->route('admin.course-catalog.courses.show', $course)
                ->with('status', __('Der Kurs liegt nicht im Papierkorb.'));
        }

        $course->restore();

        return redirect()
            ->route('admin.course-catalog.courses.show', $course)
            ->with('status', __('Kurs wurde wiederhergestellt.'));
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
