<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Enums\AiCourseGenerationEventType;
use App\Domain\CourseCatalog\Models\AiCourseGenerationEvent;
use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Models\User;

class AiCourseGenerationEventLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(
        AiCourseGenerationSession $session,
        AiCourseGenerationEventType $type,
        ?User $user,
        array $meta = []
    ): AiCourseGenerationEvent {
        $payload = $meta;
        if (isset($payload['draft_payload']) && is_array($payload['draft_payload'])) {
            $payload['draft_payload_hash'] = hash('sha256', json_encode($payload['draft_payload'], JSON_THROW_ON_ERROR));
            unset($payload['draft_payload']);
        }

        return $session->events()->create([
            'user_id' => $user?->id,
            'type' => $type,
            'meta' => $payload !== [] ? $payload : null,
        ]);
    }
}
