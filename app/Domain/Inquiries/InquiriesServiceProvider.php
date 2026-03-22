<?php

namespace App\Domain\Inquiries;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class InquiriesServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
