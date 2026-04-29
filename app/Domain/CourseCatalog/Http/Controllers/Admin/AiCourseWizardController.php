<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseKeyword;
use App\Domain\CourseCatalog\Models\CourseKeywordAnalysis;
use App\Domain\CourseCatalog\Services\AiCourseWizardService;
use App\Domain\CourseCatalog\Services\CourseContentGenerationService;
use App\Domain\CourseCatalog\Services\FieldRegenerationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiCourseWizardController extends Controller
{
    public function __construct(
        private readonly AiCourseWizardService $wizardService,
        private readonly FieldRegenerationService $fieldRegeneration,
        private readonly CourseContentGenerationService $contentGeneration,
    ) {}

    public function keywordDiscovery(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'subtopics' => ['nullable', 'array'],
            'subtopics.*' => ['string', 'max:255'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:64'],
            'duration_days' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'focus' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $this->wizardService->runKeywordDiscovery($data, $request->user());

        return response()->json($result);
    }

    public function saveSelection(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:course_keyword_analyses,id'],
            'selected_keywords' => ['nullable', 'array'],
            'selected_keywords.*' => ['string', 'max:255'],
            'selected_primary_keyword' => ['nullable', 'string', 'max:255'],
            'selected_clusters' => ['nullable', 'array'],
        ]);

        $analysis = CourseKeywordAnalysis::query()->findOrFail((int) $data['analysis_id']);
        $selected = is_array($data['selected_keywords'] ?? null) ? $data['selected_keywords'] : [];
        $analysis->update([
            'selected_keywords' => $selected,
            'selected_primary_keyword' => $data['selected_primary_keyword'] ?? ($selected[0] ?? null),
            'selected_clusters' => $data['selected_clusters'] ?? [],
        ]);

        CourseKeyword::query()->where('analysis_id', $analysis->id)->update(['selected' => false]);
        if ($selected !== []) {
            CourseKeyword::query()
                ->where('analysis_id', $analysis->id)
                ->whereIn('keyword', $selected)
                ->update(['selected' => true]);
        }

        return response()->json(['ok' => true]);
    }

    public function regenerateField(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'field_name' => ['required', 'string', 'max:100'],
            'current_context' => ['nullable', 'array'],
            'selected_keywords' => ['nullable', 'array'],
            'course_context' => ['nullable', 'array'],
        ]);

        $context = array_merge(
            is_array($data['current_context'] ?? null) ? $data['current_context'] : [],
            ['selected_keywords' => is_array($data['selected_keywords'] ?? null) ? $data['selected_keywords'] : []],
            is_array($data['course_context'] ?? null) ? $data['course_context'] : []
        );

        $value = $this->fieldRegeneration->regenerate((string) $data['field_name'], $context);

        return response()->json(['field_name' => $data['field_name'], 'value' => $value]);
    }

    public function regenerateSection(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'analysis_id' => ['required', 'integer', 'exists:course_keyword_analyses,id'],
            'section' => ['required', 'string', 'in:seo,base,details'],
            'selected_keywords' => ['nullable', 'array'],
            'selected_keywords.*' => ['string', 'max:255'],
            'generation_input' => ['nullable', 'array'],
        ]);

        $analysis = CourseKeywordAnalysis::query()->findOrFail((int) $data['analysis_id']);
        $input = [
            'topic' => $analysis->topic,
            'subtopics' => is_array($analysis->subtopics) ? $analysis->subtopics : [],
        ];
        $input = array_merge($input, is_array($data['generation_input'] ?? null) ? $data['generation_input'] : []);

        $selectedKeywords = is_array($data['selected_keywords'] ?? null) && $data['selected_keywords'] !== []
            ? array_values($data['selected_keywords'])
            : (is_array($analysis->selected_keywords) ? array_values($analysis->selected_keywords) : []);

        $payload = $this->contentGeneration->regenerateSection((string) $data['section'], $input, $selectedKeywords);

        return response()->json([
            'section' => $data['section'],
            'payload' => $payload,
        ]);
    }
}

