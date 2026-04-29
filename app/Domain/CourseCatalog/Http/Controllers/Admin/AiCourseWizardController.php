<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseKeyword;
use App\Domain\CourseCatalog\Models\CourseKeywordAnalysis;
use App\Domain\CourseCatalog\Services\AiCourseWizardService;
use App\Domain\CourseCatalog\Services\CourseContentGenerationService;
use App\Domain\CourseCatalog\Services\FieldRegenerationService;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiCourseWizardController extends Controller
{
    private const REGENERATE_PROMPT_USE_CASE = 'course-wizard-regenerate';

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
            'custom_keywords' => ['nullable', 'array', 'max:50'],
            'custom_keywords.*' => ['string', 'max:255'],
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

        $customKeywords = collect(is_array($data['custom_keywords'] ?? null) ? $data['custom_keywords'] : [])
            ->map(static fn ($keyword) => trim((string) $keyword))
            ->filter(static fn ($keyword) => $keyword !== '')
            ->unique(static fn ($keyword) => mb_strtolower($keyword))
            ->values();

        if ($customKeywords->isNotEmpty()) {
            $existing = CourseKeyword::query()
                ->where('analysis_id', $analysis->id)
                ->get(['id', 'keyword']);

            $existingMap = [];
            foreach ($existing as $row) {
                $existingMap[mb_strtolower((string) $row->keyword)] = $row;
            }

            foreach ($customKeywords as $keyword) {
                $normalized = mb_strtolower($keyword);
                $isSelected = in_array($keyword, $selected, true);
                if (isset($existingMap[$normalized])) {
                    /** @var CourseKeyword $record */
                    $record = $existingMap[$normalized];
                    $record->selected = $isSelected;
                    $record->source = ['custom'];
                    $record->save();
                    continue;
                }

                CourseKeyword::query()->create([
                    'analysis_id' => $analysis->id,
                    'keyword' => $keyword,
                    'type' => 'custom',
                    'intent' => 'custom',
                    'source' => ['custom'],
                    'relevance_score' => 5,
                    'commercial_score' => 5,
                    'course_fit_score' => 5,
                    'selected' => $isSelected,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function regenerateField(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'field_name' => ['required', 'string', 'max:100'],
            'field_path' => ['required', 'string', 'max:150'],
            'analysis_id' => ['required', 'integer', 'exists:course_keyword_analyses,id'],
            'generation_input' => ['nullable', 'array'],
            'current_context' => ['nullable', 'array'],
            'selected_keywords' => ['nullable', 'array'],
            'selected_keywords.*' => ['string', 'max:255'],
            'course_context' => ['nullable', 'array'],
            'prompt_id' => ['nullable', 'integer', 'exists:ai_prompts,id'],
            'prompt_text' => ['nullable', 'string', 'max:12000'],
            'save_prompt' => ['nullable', 'boolean'],
            'prompt_title' => ['nullable', 'string', 'max:160'],
        ]);

        $analysis = CourseKeywordAnalysis::query()->findOrFail((int) $data['analysis_id']);
        $analysisSelected = CourseKeyword::query()
            ->where('analysis_id', $analysis->id)
            ->where('selected', true)
            ->pluck('keyword')
            ->map(static fn ($keyword) => (string) $keyword)
            ->values()
            ->all();

        $requestedSelected = is_array($data['selected_keywords'] ?? null) ? array_values($data['selected_keywords']) : [];
        $resolvedSelected = $requestedSelected !== [] ? $requestedSelected : $analysisSelected;

        $baseContext = [
            'topic' => $analysis->topic,
            'subtopics' => is_array($analysis->subtopics) ? $analysis->subtopics : [],
            'selected_keywords' => $resolvedSelected,
            'selected_primary_keyword' => $resolvedSelected[0] ?? $analysis->selected_primary_keyword,
            'field_path' => (string) $data['field_path'],
            'field_name' => (string) $data['field_name'],
        ];

        $context = array_merge(
            $baseContext,
            is_array($data['generation_input'] ?? null) ? $data['generation_input'] : [],
            is_array($data['current_context'] ?? null) ? $data['current_context'] : [],
            is_array($data['course_context'] ?? null) ? $data['course_context'] : []
        );

        $promptText = $this->resolvePromptText(
            $data['prompt_id'] ?? null,
            $data['prompt_text'] ?? null
        );
        if ($promptText !== null) {
            $context['prompt_text'] = $promptText;
        }

        if (($data['save_prompt'] ?? false) && is_string($data['prompt_text'] ?? null) && trim((string) $data['prompt_text']) !== '') {
            $this->savePromptToLibrary(
                $request,
                (string) ($data['prompt_title'] ?? ''),
                (string) $data['prompt_text']
            );
        }

        $result = $this->fieldRegeneration->regenerateWithMeta((string) $data['field_name'], $context);

        return response()->json([
            'field_name' => $data['field_name'],
            'value' => $result['value'],
            'source' => $result['source'],
        ]);
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
            'prompt_id' => ['nullable', 'integer', 'exists:ai_prompts,id'],
            'prompt_text' => ['nullable', 'string', 'max:12000'],
            'save_prompt' => ['nullable', 'boolean'],
            'prompt_title' => ['nullable', 'string', 'max:160'],
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

        $promptText = $this->resolvePromptText(
            $data['prompt_id'] ?? null,
            $data['prompt_text'] ?? null
        );
        if ($promptText !== null) {
            $input['prompt_text'] = $promptText;
        }
        if (($data['save_prompt'] ?? false) && is_string($data['prompt_text'] ?? null) && trim((string) $data['prompt_text']) !== '') {
            $this->savePromptToLibrary(
                $request,
                (string) ($data['prompt_title'] ?? ''),
                (string) $data['prompt_text']
            );
        }

        $payload = $this->contentGeneration->regenerateSection((string) $data['section'], $input, $selectedKeywords);

        return response()->json([
            'section' => $data['section'],
            'payload' => $payload,
        ]);
    }

    public function promptLibrary(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $prompts = AiPrompt::query()
            ->where('is_active', true)
            ->where('use_case', self::REGENERATE_PROMPT_USE_CASE)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'description', 'body']);

        return response()->json([
            'prompts' => $prompts->map(static fn (AiPrompt $prompt) => [
                'id' => $prompt->id,
                'title' => $prompt->title,
                'description' => $prompt->description,
                'body' => $prompt->body,
            ])->values(),
        ]);
    }

    public function savePrompt(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:12000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $prompt = AiPrompt::query()->create([
            'title' => trim((string) $data['title']),
            'slug' => Str::slug((string) $data['title']).'-'.Str::lower(Str::random(6)),
            'use_case' => self::REGENERATE_PROMPT_USE_CASE,
            'body' => trim((string) $data['body']),
            'placeholder_definitions' => [],
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'prompt' => [
                'id' => $prompt->id,
                'title' => $prompt->title,
                'description' => $prompt->description,
                'body' => $prompt->body,
            ],
        ], 201);
    }

    private function resolvePromptText(mixed $promptId, mixed $promptText): ?string
    {
        $inlinePrompt = trim((string) $promptText);
        if ($inlinePrompt !== '') {
            return $inlinePrompt;
        }
        if (is_numeric($promptId)) {
            $prompt = AiPrompt::query()->find((int) $promptId);
            if ($prompt && $prompt->is_active) {
                return (string) $prompt->body;
            }
        }

        return null;
    }

    private function savePromptToLibrary(Request $request, string $title, string $body): void
    {
        $cleanBody = trim($body);
        if ($cleanBody === '') {
            return;
        }
        $cleanTitle = trim($title);
        $titleValue = $cleanTitle !== '' ? $cleanTitle : 'AI2 Regenerate '.now()->format('Y-m-d H:i');
        $request->validate([
            'prompt_title' => ['nullable', 'string', 'max:160'],
        ]);

        AiPrompt::query()->create([
            'title' => $titleValue,
            'slug' => Str::slug($titleValue).'-'.Str::lower(Str::random(6)),
            'use_case' => self::REGENERATE_PROMPT_USE_CASE,
            'body' => $cleanBody,
            'placeholder_definitions' => [],
            'description' => 'Erstellt aus AI Generator 2 Regenerate.',
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }
}

