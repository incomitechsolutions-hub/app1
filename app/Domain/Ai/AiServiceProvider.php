<?php

namespace App\Domain\Ai;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class AiServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
