<?php

namespace App\Domain\Identity;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class IdentityServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
