<?php

namespace App\Domain\PromptManagement\Enums;

use Illuminate\Support\Str;

enum PromptUseCase: string
{
    case CourseCreation = 'course_creation';
    case CategoryManagement = 'category_management';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::CourseCreation => 'Kurserstellung',
            self::CategoryManagement => 'Kategorien',
            self::General => 'Allgemein',
        };
    }

    /** Human-readable label for a stored slug (built-in enum or custom). */
    public static function labelForValue(string $value): string
    {
        $enum = self::tryFrom($value);

        return $enum !== null ? $enum->label() : Str::headline($value);
    }
}
