<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Services\AiCourseGenerationSessionService;
use App\Domain\CourseCatalog\Services\WebsiteCrawlExtractionService;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Services\PromptService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CourseCrawlController extends Controller
{
    public function __construct(
        private readonly WebsiteCrawlExtractionService $crawlService,
        private readonly PromptService $prompts,
        private readonly AiCourseGenerationSessionService $sessionService
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $request->validate([
            'source_url' => ['required', 'string', 'url', 'max:2048'],
        ]);

        try {
            $sourceUrl = (string) $request->input('source_url');
            $crawl = $this->crawlService->extractSinglePage($sourceUrl);

            $lockedTitle = trim((string) ($crawl['h1'] ?: $crawl['title']));
            $lockedSubtitle = trim((string) ($crawl['meta_description'] ?? ''));

            $brief = $this->buildBriefFromCrawl($crawl);
            $template = $this->prompts->activeForUseCase(PromptUseCase::CourseCreation)->first();

            $session = $this->sessionService->createFromWizardStart(
                $request->user(),
                $template,
                [],
                $brief,
                [
                    'crawl' => $crawl,
                    'locked_title' => $lockedTitle !== '' ? $lockedTitle : null,
                    'locked_subtitle' => $lockedSubtitle !== '' ? $lockedSubtitle : null,
                ]
            );

            $session = $this->sessionService->runInitialAiGeneration($session, $request->user());
            if ($session->last_error) {
                return redirect()
                    ->route('admin.course-catalog.courses.ai-generation.create')
                    ->with('ai_error', $session->last_error);
            }

            return redirect()
                ->route('admin.course-catalog.courses.ai-generation.wizard', $session)
                ->with('status', __('Webseite gecrawlt und KI-Entwurf erstellt.'));
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.course-catalog.courses.create')
                ->withInput()
                ->with('crawl_info', __('Webseite konnte nicht verarbeitet werden: :message', [
                    'message' => $e->getMessage(),
                ]));
        }
    }

    /**
     * @param  array<string, mixed>  $crawl
     */
    private function buildBriefFromCrawl(array $crawl): string
    {
        $keywords = is_array($crawl['seo_keywords'] ?? null) ? implode(', ', $crawl['seo_keywords']) : '';
        $headings = is_array($crawl['headings'] ?? null) ? implode(' | ', $crawl['headings']) : '';

        return trim(implode("\n", array_filter([
            'Kurs aus Website-Inhalten generieren.',
            'Quelle: '.(string) ($crawl['source_url'] ?? ''),
            'Titel: '.(string) ($crawl['title'] ?? ''),
            'H1: '.(string) ($crawl['h1'] ?? ''),
            'Meta Description: '.(string) ($crawl['meta_description'] ?? ''),
            $keywords !== '' ? 'SEO Keywords: '.$keywords : '',
            $headings !== '' ? 'Headings: '.$headings : '',
            'Inhaltsauszug:',
            (string) ($crawl['body_excerpt'] ?? ''),
        ])));
    }
}
