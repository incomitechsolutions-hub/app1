<?php

namespace App\Domain\CourseCatalog\Enums;

enum AiCourseGenerationSessionStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case ReadyToFinalize = 'ready_to_finalize';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}
