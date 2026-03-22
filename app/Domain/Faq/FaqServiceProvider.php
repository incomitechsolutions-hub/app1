<?php

namespace App\Domain\Faq;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class FaqServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
