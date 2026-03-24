<?php

namespace App\Domain\CourseCatalog\Enums;

enum DeliveryMode: string
{
    case LiveOnline = 'live_online';
    case SelfStudy = 'self_study';
    case BlendedLearning = 'blended_learning';
}
