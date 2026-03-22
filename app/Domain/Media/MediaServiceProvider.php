<?php

namespace App\Domain\Media;

use App\Domain\Shared\Providers\DomainModuleServiceProvider;

class MediaServiceProvider extends DomainModuleServiceProvider
{
    protected function moduleBasePath(): string
    {
        return __DIR__;
    }
}
