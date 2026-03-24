<?php

namespace App\Domain\CourseCatalog\Enums;

enum DeliveryFormat: string
{
    case Online = 'online';
    case Presence = 'presence';
    case Hybrid = 'hybrid';
}
