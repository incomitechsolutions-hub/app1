<?php

namespace App\Domain\CourseCatalog\Policies;

use App\Domain\CourseCatalog\Enums\AiCourseGenerationSessionStatus;
use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Models\User;

class AiCourseGenerationSessionPolicy
{
    public function view(User $user, AiCourseGenerationSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function update(User $user, AiCourseGenerationSession $session): bool
    {
        if ($user->id !== $session->user_id) {
            return false;
        }

        return ! in_array($session->status, [
            AiCourseGenerationSessionStatus::Completed,
            AiCourseGenerationSessionStatus::Cancelled,
            AiCourseGenerationSessionStatus::Expired,
        ], true);
    }

    public function finalize(User $user, AiCourseGenerationSession $session): bool
    {
        if ($user->id !== $session->user_id) {
            return false;
        }

        return in_array($session->status, [
            AiCourseGenerationSessionStatus::InReview,
            AiCourseGenerationSessionStatus::ReadyToFinalize,
        ], true);
    }
}
