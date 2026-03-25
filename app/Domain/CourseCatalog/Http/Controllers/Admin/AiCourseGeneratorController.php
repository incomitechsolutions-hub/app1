<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Http\Requests\Admin\StoreCourseRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Services\AiCourseGeneratorService;
use App\Domain\CourseCatalog\Services\CourseService;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Services\PromptService;
use App\Domain\Taxonomy\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiCourseGeneratorController extends Controller
{
    public function __construct(
        private readonly AiCourseGeneratorService $generator,
        private readonly CourseService $courses,
        private readonly PromptService $prompts
    ) {}

    public function create(): View
    {
        $this->authorize('create', Course::class);

        $templates = $this->prompts->activeForUseCase(PromptUseCase::CourseCreation);

        return view('admin.courses.ai-generator', [
            'templates' => $templates,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'brief' => ['required', 'string', 'max:12000'],
            'ai_prompt_id' => ['nullable', 'integer', 'exists:ai_prompts,id'],
        ]);

        $result = $this->generator->generateDraft(
            $validated['brief'],
            isset($validated['ai_prompt_id']) ? (int) $validated['ai_prompt_id'] : null
        );

        if (! $result['ok']) {
            return redirect()
                ->route('admin.course-catalog.courses.ai-generator')
                ->withInput()
                ->with('ai_error', $result['error'] ?? __('Fehler.'));
        }

        session(['ai_course_draft' => $result['draft']]);

        return redirect()->route('admin.course-catalog.courses.ai-generator.review');
    }

    public function review(): View|RedirectResponse
    {
        $this->authorize('create', Course::class);

        $draft = session('ai_course_draft');
        if (! is_array($draft)) {
            return redirect()
                ->route('admin.course-catalog.courses.ai-generator')
                ->with('ai_error', __('Kein Entwurf gefunden. Bitte erneut generieren.'));
        }

        return view('admin.courses.ai-generator-review', [
            'draft' => $draft,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $draft = session('ai_course_draft');
        if (! is_array($draft)) {
            return redirect()
                ->route('admin.course-catalog.courses.ai-generator')
                ->with('ai_error', __('Sitzung abgelaufen. Bitte erneut generieren.'));
        }

        $request->merge([
            'title' => $request->input('title', $draft['title'] ?? ''),
            'subtitle' => $request->input('subtitle', null),
            'slug' => $request->input('slug', $draft['slug'] ?? ''),
            'short_description' => $request->input('short_description', $draft['short_description'] ?? ''),
            'long_description' => $request->input('long_description', $draft['long_description'] ?? ''),
            'duration_days' => $request->input('duration_days', $draft['duration_days'] ?? null),
            'price' => $request->input('price', $draft['price'] ?? null),
            'language_code' => $request->input('language_code', $draft['language_code'] ?? 'de'),
            'currency_code' => $request->input('currency_code', 'EUR'),
            'status' => CourseStatus::Draft->value,
            'primary_category_id' => $request->filled('primary_category_id') ? (int) $request->input('primary_category_id') : null,
            'difficulty_level_id' => $request->input('difficulty_level_id'),
            'tag_ids' => $request->input('tag_ids', []),
            'audience_ids' => $request->input('audience_ids', []),
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
            'faqs' => [],
            'course_relations' => [],
            'course_discount_tiers' => [],
            'is_featured' => false,
            'media_icon_enabled' => false,
            'media_header_enabled' => false,
            'media_video_enabled' => false,
            'media_gallery_enabled' => false,
            'published_at' => null,
            'delivery_format' => null,
            'external_course_code' => null,
            'seo' => [
                'seo_title' => $request->input('seo.seo_title', $draft['seo_title'] ?? ''),
                'meta_description' => $request->input('seo.meta_description', $draft['meta_description'] ?? ''),
            ],
        ]);

        $storeRequest = StoreCourseRequest::createFrom($request);
        $storeRequest->setContainer(app())->setRedirector(app('redirect'));
        $storeRequest->validateResolved();

        $course = $this->courses->create($storeRequest->validated());

        session()->forget('ai_course_draft');

        return redirect()
            ->route('admin.course-catalog.courses.edit', $course)
            ->with('status', __('Kurs aus KI-Entwurf angelegt.'));
    }
}
