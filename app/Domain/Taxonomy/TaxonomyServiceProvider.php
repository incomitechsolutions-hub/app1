<?php

namespace App\Domain\Taxonomy;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class TaxonomyServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
