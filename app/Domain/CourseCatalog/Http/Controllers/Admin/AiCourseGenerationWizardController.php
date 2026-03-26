<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Enums\AiCourseGenerationSessionStatus;
use App\Domain\CourseCatalog\Http\Requests\Admin\FinalizeAiCourseRequest;
use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\CourseCatalog\Services\AiCourseGenerationSessionService;
use App\Domain\Media\Models\MediaAsset;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\DifficultyLevel;
use App\Domain\Taxonomy\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiCourseGenerationWizardController extends Controller
{
    public function __construct(
        private readonly AiCourseGenerationSessionService $sessionService
    ) {}

    public function wizard(AiCourseGenerationSession $aiCourseGenerationSession): View|RedirectResponse
    {
        $this->authorize('update', $aiCourseGenerationSession);

        if ($aiCourseGenerationSession->status === AiCourseGenerationSessionStatus::Completed && $aiCourseGenerationSession->resulting_course_id) {
            return redirect()
                ->route('admin.course-catalog.courses.edit', $aiCourseGenerationSession->resulting_course_id)
                ->with('status', __('Dieser Entwurf wurde bereits als Kurs gespeichert.'));
        }

        if ($aiCourseGenerationSession->draft_payload === null) {
            return redirect()
                ->route('admin.course-catalog.courses.ai-generation.create')
                ->with('ai_error', __('Kein KI-Entwurf vorhanden. Bitte erneut starten.'));
        }

        return view('admin.courses.ai-generation.wizard', array_merge(
            [
                'session' => $aiCourseGenerationSession,
                'draft' => $aiCourseGenerationSession->draft_payload ?? [],
                'crawlContext' => $this->crawlContext($aiCourseGenerationSession),
            ],
            $this->formOptions()
        ));
    }

    public function updateDraft(Request $request, AiCourseGenerationSession $aiCourseGenerationSession): RedirectResponse
    {
        $this->authorize('update', $aiCourseGenerationSession);

        $request->validate([
            'draft' => ['required', 'array'],
        ]);

        $merged = $this->mergeDraftPayload($aiCourseGenerationSession->draft_payload ?? [], $request->input('draft', []));

        $this->sessionService->saveDraftPayload($aiCourseGenerationSession, $request->user(), $merged);

        return redirect()
            ->route('admin.course-catalog.courses.ai-generation.wizard', $aiCourseGenerationSession)
            ->with('status', __('Entwurf gespeichert.'));
    }

    public function regenerate(Request $request, AiCourseGenerationSession $aiCourseGenerationSession): RedirectResponse
    {
        $this->authorize('update', $aiCourseGenerationSession);

        $data = $request->validate([
            'section' => ['required', 'string', 'max:64'],
            'hint' => ['nullable', 'string', 'max:4000'],
        ]);

        $result = $this->sessionService->regenerateSection(
            $aiCourseGenerationSession,
            $request->user(),
            $data['section'],
            (string) ($data['hint'] ?? '')
        );

        if (! $result['ok']) {
            return redirect()
                ->back()
                ->with('ai_error', $result['error'] ?? __('Regenerierung fehlgeschlagen.'));
        }

        return redirect()
            ->route('admin.course-catalog.courses.ai-generation.wizard', $result['session'])
            ->with('status', __('Abschnitt neu generiert.'));
    }

    public function confirmSteps(Request $request, AiCourseGenerationSession $aiCourseGenerationSession): RedirectResponse
    {
        $this->authorize('update', $aiCourseGenerationSession);

        $data = $request->validate([
            'steps' => ['nullable', 'array'],
            'steps.*' => ['boolean'],
        ]);

        $this->sessionService->confirmSteps(
            $aiCourseGenerationSession,
            $request->user(),
            $data['steps'] ?? null
        );

        return redirect()
            ->back()
            ->with('status', __('Schritte bestätigt.'));
    }

    public function finalize(FinalizeAiCourseRequest $request, AiCourseGenerationSession $aiCourseGenerationSession): RedirectResponse
    {
        $this->authorize('finalize', $aiCourseGenerationSession);

        $validated = $request->validated();

        $merged = array_merge($aiCourseGenerationSession->draft_payload ?? [], $validated);

        $aiCourseGenerationSession->update([
            'draft_payload' => $merged,
        ]);

        $course = $this->sessionService->finalize($aiCourseGenerationSession->fresh(), $request->user());

        return redirect()
            ->route('admin.course-catalog.courses.edit', $course)
            ->with('status', __('Kurs aus KI-Entwurf angelegt.'));
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     */
    protected function mergeDraftPayload(array $base, array $patch): array
    {
        $out = $base;
        foreach ($patch as $key => $value) {
            if ($key === 'seo' && is_array($value) && isset($base['seo']) && is_array($base['seo'])) {
                $out['seo'] = array_merge($base['seo'], $value);
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
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
            'catalogDefaults' => $globals,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function crawlContext(AiCourseGenerationSession $session): ?array
    {
        $raw = $session->full_prompt_audit;
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $audit = json_decode($raw, true);
        if (! is_array($audit) || ! is_array($audit['crawl'] ?? null)) {
            return null;
        }

        $crawl = $audit['crawl'];
        $crawl['locked_title'] = is_string($audit['locked_title'] ?? null) ? $audit['locked_title'] : null;
        $crawl['locked_subtitle'] = is_string($audit['locked_subtitle'] ?? null) ? $audit['locked_subtitle'] : null;

        return $crawl;
    }
}
