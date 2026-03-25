<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

/**
 * Same validation rules as creating a course; used when persisting an AI draft into courses.
 */
class FinalizeAiCourseRequest extends StoreCourseRequest
{
}
