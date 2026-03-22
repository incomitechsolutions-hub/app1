<?php

namespace App\Domain\CourseCatalog\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case SeoReview = 'seo_review';
    case Published = 'published';
}
