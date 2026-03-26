<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\KeywordResearchRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Seo\Services\KeywordResearchOrchestrator;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class KeywordResearchController extends Controller
{
    public function __construct(
        private readonly KeywordResearchOrchestrator $keywordResearch
    ) {}

    public function __invoke(KeywordResearchRequest $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $result = $this->keywordResearch->execute((string) $request->validated('courseIdea'));

        return response()->json([
            'courseIdea' => $result['courseIdea'],
            'primary_keyword' => $result['primary_keyword'],
            'keyword_variants' => $result['keyword_variants'],
            'supporting_keywords' => $result['supporting_keywords'],
            'all_keywords' => $result['all_keywords'],
            'ranked' => $result['ranked'],
            'payload' => $result['payload'],
        ]);
    }
}
