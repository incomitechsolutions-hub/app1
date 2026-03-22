<?php

namespace App\Domain\Locations;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class LocationsServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
