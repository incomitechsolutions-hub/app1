<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\StoreCourseRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\UpdateCourseRequest;
use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Services\CourseService;
use App\Domain\Media\Models\MediaAsset;
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
            ->with(['primaryCategory', 'difficultyLevel'])
            ->latest();

        if ($trashed) {
            $query->onlyTrashed();
        }

        if ($request->query('featured') === '1') {
            $query->where('is_featured', true);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q): void {
                $sub->where('title', 'like', '%'.$q.'%')
                    ->orWhere('slug', 'like', '%'.$q.'%')
                    ->orWhere('external_course_code', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('primary_category_id', (int) $request->query('category_id'));
        }

        if ($request->filled('difficulty_level_id')) {
            $query->where('difficulty_level_id', (int) $request->query('difficulty_level_id'));
        }

        if ($request->filled('delivery_format')) {
            $query->where('delivery_format', (string) $request->query('delivery_format'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $courses = $query->paginate(20)->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'trashed' => $trashed,
            'featuredFilter' => $request->query('featured') === '1',
            'categories' => Category::query()->orderBy('name')->get(),
            'difficultyLevels' => DifficultyLevel::query()->orderBy('sort_order')->get(),
            'courseStatuses' => CourseStatus::cases(),
            'deliveryFormats' => DeliveryFormat::cases(),
            'filters' => [
                'q' => $q,
                'category_id' => $request->query('category_id'),
                'difficulty_level_id' => $request->query('difficulty_level_id'),
                'delivery_format' => $request->query('delivery_format'),
                'status' => $request->query('status'),
            ],
        ]);
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
            'tags',
            'audiences',
            'modules',
            'learningObjectives',
            'prerequisites',
            'faqs',
            'courseRelations.relatedCourse',
            'openClassrooms',
            'programs',
            'seoMeta',
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
            'primaryCategory',
            'tags',
            'audiences',
            'modules',
            'learningObjectives',
            'prerequisites',
            'discountTiers',
            'faqs',
            'courseRelations.relatedCourse',
            'openClassrooms',
            'programs',
            'seoMeta',
        ]);

        return view('admin.courses.edit', array_merge(
            ['course' => $course, 'seoMeta' => $course->seoMeta],
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
        $globals = CourseCatalogGlobalSetting::singleton();

        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'difficultyLevels' => DifficultyLevel::query()->orderBy('sort_order')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'audiences' => Audience::query()->orderBy('name')->get(),
            'coursesForRelations' => Course::query()->orderBy('title')->get(['id', 'title']),
            'mediaAssets' => MediaAsset::query()->orderByDesc('id')->limit(200)->get(),
            'seoMeta' => null,
            'catalogDefaults' => $globals,
        ];
    }
}
