<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\CourseCatalog\Enums\AiCourseGenerationEventType;
use App\Domain\CourseCatalog\Enums\AiCourseGenerationSessionStatus;
use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AiCourseGenerationSessionService
{
    public function __construct(
        private readonly AiCourseGenerationEventLogger $events,
        private readonly AiCourseGenerationPromptBuilderService $promptBuilder,
        private readonly PromptPlaceholderInterpolationService $interpolation,
        private readonly AiCourseGeneratorService $generator,
        private readonly AiCourseTaxonomySuggestionService $taxonomySuggestion,
        private readonly PersistAiGeneratedCourseService $persistAiCourse,
    ) {}

    /**
     * @param  array<string, string>  $placeholderValues
     */
    public function createFromWizardStart(
        User $user,
        ?AiPrompt $template,
        array $placeholderValues,
        string $brief
    ): AiCourseGenerationSession {
        $templateBody = null;
        $snapshot = null;
        if ($template !== null) {
            $templateBody = $template->body;
            $snapshot = [
                'id' => $template->id,
                'title' => $template->title,
                'slug' => $template->slug,
                'use_case' => $template->use_case->value,
                'body' => $template->body,
                'placeholder_definitions' => $template->placeholder_definitions,
            ];
        }

        $keys = $template !== null ? $this->interpolation->resolvePlaceholderKeys($template) : [];
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $placeholderValues[$key] ?? '';
        }

        $built = $this->promptBuilder->build($templateBody, $values, $brief);

        $audit = [
            'compiled_prompt' => $built['compiled_prompt'],
            'model' => (string) (AiSetting::singleton()->default_model ?: 'gpt-4o-mini'),
        ];

        $session = AiCourseGenerationSession::query()->create([
            'user_id' => $user->id,
            'ai_prompt_id' => $template?->id,
            'status' => AiCourseGenerationSessionStatus::Draft,
            'template_snapshot' => $snapshot,
            'placeholder_input' => $values,
            'brief' => $brief,
            'interpolated_body' => $built['interpolated_body'],
            'compiled_prompt' => $built['compiled_prompt'],
            'full_prompt_audit' => json_encode($audit, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'draft_payload' => null,
            'confirmed_steps' => null,
            'last_regenerated_section' => null,
            'resulting_course_id' => null,
            'last_error' => null,
            'expires_at' => now()->addDays(7),
        ]);

        $this->events->log($session, AiCourseGenerationEventType::SessionCreated, $user, []);
        $this->events->log($session, AiCourseGenerationEventType::PromptCompiled, $user, [
            'compiled_prompt_length' => strlen($built['compiled_prompt']),
        ]);

        return $session;
    }

    public function runInitialAiGeneration(AiCourseGenerationSession $session, User $user): AiCourseGenerationSession
    {
        $this->events->log($session, AiCourseGenerationEventType::AiRequestStarted, $user, [
            'phase' => 'full_draft',
        ]);

        $started = microtime(true);
        $result = $this->generator->generateFullStructuredDraft($session->compiled_prompt);
        $ms = (int) round((microtime(true) - $started) * 1000);

        if (! $result['ok'] || empty($result['draft_payload'])) {
            $session->update([
                'last_error' => $result['error'] ?? __('Unbekannter Fehler.'),
                'status' => AiCourseGenerationSessionStatus::Draft,
            ]);
            $this->events->log($session, AiCourseGenerationEventType::AiRequestFailed, $user, [
                'duration_ms' => $ms,
                'error' => $result['error'] ?? 'empty',
            ]);

            return $session->fresh();
        }

        $draftPayload = $result['draft_payload'];
        $taxStarted = microtime(true);
        $draftPayload = $this->taxonomySuggestion->applySuggestionsIfNeeded($draftPayload, (string) $session->brief);
        $taxMs = (int) round((microtime(true) - $taxStarted) * 1000);

        if (! empty($draftPayload['ai_taxonomy_warning'] ?? null)) {
            $this->events->log($session, AiCourseGenerationEventType::TaxonomySuggestionFailed, $user, [
                'duration_ms' => $taxMs,
                'warning' => $draftPayload['ai_taxonomy_warning'],
            ]);
        } else {
            $this->events->log($session, AiCourseGenerationEventType::TaxonomySuggestionSucceeded, $user, [
                'duration_ms' => $taxMs,
                'primary_category_id' => $draftPayload['primary_category_id'] ?? null,
                'audience_ids' => $draftPayload['audience_ids'] ?? [],
            ]);
        }

        $session->update([
            'draft_payload' => $draftPayload,
            'status' => AiCourseGenerationSessionStatus::InReview,
            'last_error' => null,
        ]);

        $this->events->log($session, AiCourseGenerationEventType::AiRequestSucceeded, $user, [
            'duration_ms' => $ms,
            'draft_payload' => $draftPayload,
        ]);

        return $session->fresh();
    }

    /**
     * @param  array<string, mixed>  $draftPayload
     */
    public function saveDraftPayload(AiCourseGenerationSession $session, User $user, array $draftPayload): void
    {
        $session->update([
            'draft_payload' => $draftPayload,
            'status' => AiCourseGenerationSessionStatus::InReview,
        ]);

        $this->events->log($session, AiCourseGenerationEventType::DraftUpdatedManual, $user, [
            'draft_payload' => $draftPayload,
        ]);
    }

    /**
     * @return array{ok: bool, session: AiCourseGenerationSession, error?: string}
     */
    public function regenerateSection(
        AiCourseGenerationSession $session,
        User $user,
        string $section,
        string $hint
    ): array {
        $draft = $session->draft_payload ?? [];
        $this->events->log($session, AiCourseGenerationEventType::AiRequestStarted, $user, [
            'section' => $section,
        ]);

        $started = microtime(true);
        $result = $this->generator->regenerateSection($section, $draft, $hint);
        $ms = (int) round((microtime(true) - $started) * 1000);

        if (! $result['ok'] || empty($result['draft_payload'])) {
            $session->update([
                'last_error' => $result['error'] ?? __('Unbekannter Fehler.'),
            ]);
            $this->events->log($session, AiCourseGenerationEventType::AiRequestFailed, $user, [
                'section' => $section,
                'duration_ms' => $ms,
                'error' => $result['error'] ?? 'empty',
            ]);

            return ['ok' => false, 'session' => $session->fresh(), 'error' => $result['error'] ?? __('Fehler.')];
        }

        $session->update([
            'draft_payload' => $result['draft_payload'],
            'last_regenerated_section' => $section,
            'status' => AiCourseGenerationSessionStatus::InReview,
            'last_error' => null,
        ]);

        $this->events->log($session, AiCourseGenerationEventType::SectionRegenerated, $user, [
            'section' => $section,
            'duration_ms' => $ms,
            'draft_payload' => $result['draft_payload'],
        ]);

        return ['ok' => true, 'session' => $session->fresh()];
    }

    /**
     * @param  array<string, bool|string>|null  $confirmedSteps
     */
    public function confirmSteps(AiCourseGenerationSession $session, User $user, ?array $confirmedSteps): void
    {
        $session->update([
            'confirmed_steps' => $confirmedSteps,
            'status' => AiCourseGenerationSessionStatus::ReadyToFinalize,
        ]);

        $this->events->log($session, AiCourseGenerationEventType::StepConfirmed, $user, [
            'confirmed_steps' => $confirmedSteps,
        ]);
    }

    public function finalize(AiCourseGenerationSession $session, User $user): Course
    {
        $this->events->log($session, AiCourseGenerationEventType::FinalizeAttempted, $user, []);

        return DB::transaction(function () use ($session, $user) {
            $course = $this->persistAiCourse->persistFromSession($session);

            $session->update([
                'status' => AiCourseGenerationSessionStatus::Completed,
                'resulting_course_id' => $course->id,
            ]);

            $this->events->log($session, AiCourseGenerationEventType::CoursePersisted, $user, [
                'course_id' => $course->id,
            ]);

            return $course;
        });
    }
}
