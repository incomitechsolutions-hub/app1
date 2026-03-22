<?php

namespace App\Domain\CourseCatalog;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class CourseCatalogServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
