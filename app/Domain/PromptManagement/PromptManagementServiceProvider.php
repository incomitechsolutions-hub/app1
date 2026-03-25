<?php

namespace App\Domain\PromptManagement;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class PromptManagementServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
