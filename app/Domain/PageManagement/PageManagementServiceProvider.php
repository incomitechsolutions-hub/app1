<?php

namespace App\Domain\PageManagement;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class PageManagementServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
