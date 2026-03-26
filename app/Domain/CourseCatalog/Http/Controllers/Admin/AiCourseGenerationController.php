<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\StoreAiCourseGenerationSessionRequest;
use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Services\AiCourseGenerationSessionService;
use App\Domain\CourseCatalog\Services\CourseKeywordPayloadBuilder;
use App\Domain\CourseCatalog\Services\PromptPlaceholderInterpolationService;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Domain\PromptManagement\Services\PromptService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiCourseGenerationController extends Controller
{
    public function __construct(
        private readonly PromptService $prompts,
        private readonly PromptPlaceholderInterpolationService $interpolation,
        private readonly AiCourseGenerationSessionService $sessionService,
        private readonly CourseKeywordPayloadBuilder $courseKeywordPayload
    ) {}

    public function create(): View
    {
        $this->authorize('create', Course::class);

        $templates = $this->prompts->activeForUseCase(PromptUseCase::CourseCreation);

        $templateMeta = [];
        foreach ($templates as $t) {
            $templateMeta[$t->id] = [
                'keys' => $this->interpolation->resolvePlaceholderKeys($t),
            ];
        }

        return view('admin.courses.ai-generation.start', [
            'templates' => $templates,
            'templateMeta' => $templateMeta,
        ]);
    }

    public function store(StoreAiCourseGenerationSessionRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $brief = (string) $request->validated('brief');
        $promptId = $request->validated('ai_prompt_id');

        $rawPlaceholders = $request->input('placeholders', []);
        if (! is_array($rawPlaceholders)) {
            $rawPlaceholders = [];
        }

        $template = null;
        if ($promptId !== null) {
            $template = AiPrompt::query()->whereKey((int) $promptId)->firstOrFail();
        }

        $values = [];
        if ($template !== null) {
            foreach ($this->interpolation->resolvePlaceholderKeys($template) as $key) {
                $v = $rawPlaceholders[$key] ?? '';
                $values[$key] = is_string($v) ? $v : '';
            }
        }

        $context = [];
        $rawKeywordData = $request->validated('keyword_data');
        if (is_array($rawKeywordData)) {
            $built = $this->courseKeywordPayload->build($brief, $rawKeywordData);
            $kd = $built['keyword_data'];
            if ($kd['primary_keyword'] !== '' || $kd['keyword_variants'] !== [] || $kd['supporting_keywords'] !== []) {
                $context['keyword_data'] = $kd;
            }
        }

        $session = $this->sessionService->createFromWizardStart(
            $request->user(),
            $template,
            $values,
            $brief,
            $context
        );

        $session = $this->sessionService->runInitialAiGeneration($session, $request->user());

        if ($session->last_error) {
            return redirect()
                ->route('admin.course-catalog.courses.ai-generation.create')
                ->withInput()
                ->with('ai_error', $session->last_error);
        }

        return redirect()
            ->route('admin.course-catalog.courses.ai-generation.wizard', $session)
            ->with('status', __('KI-Entwurf erstellt. Bitte prüfen und finalisieren.'));
    }

    public function show(AiCourseGenerationSession $aiCourseGenerationSession): View
    {
        $this->authorize('view', $aiCourseGenerationSession);

        return view('admin.courses.ai-generation.show', [
            'session' => $aiCourseGenerationSession,
        ]);
    }
}
