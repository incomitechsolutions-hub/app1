<?php

namespace App\Domain\Localization;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class LocalizationServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
