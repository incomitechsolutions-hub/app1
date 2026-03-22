<?php

namespace App\Domain\Seo;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class SeoServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
