<?php

namespace App\Domain\PromptManagement\Enums;

enum PromptUseCase: string
{
    case CourseCreation = 'course_creation';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::CourseCreation => 'Kurserstellung',
            self::General => 'Allgemein',
        };
    }
}
