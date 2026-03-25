<?php

namespace App\Domain\CourseCatalog\Enums;

enum AiCourseGenerationEventType: string
{
    case SessionCreated = 'session_created';
    case PromptCompiled = 'prompt_compiled';
    case AiRequestStarted = 'ai_request_started';
    case AiRequestSucceeded = 'ai_request_succeeded';
    case AiRequestFailed = 'ai_request_failed';
    case DraftUpdatedManual = 'draft_updated_manual';
    case SectionRegenerated = 'section_regenerated';
    case StepConfirmed = 'step_confirmed';
    case FinalizeAttempted = 'finalize_attempted';
    case CoursePersisted = 'course_persisted';
    case SessionCancelled = 'session_cancelled';
}
