<?php

namespace App\Domain\Shared\Providers;

use Illuminate\Support\ServiceProvider;

abstract class DomainModuleServiceProvider extends ServiceProvider
{
    abstract protected function moduleBasePath(): string;

    public function boot(): void
    {
        $base = rtrim($this->moduleBasePath(), DIRECTORY_SEPARATOR);

        $publicRoutes = $base.'/routes/public.php';
        if (is_file($publicRoutes)) {
            $this->loadRoutesFrom($publicRoutes);
        }

        $adminRoutes = $base.'/routes/admin.php';
        if (is_file($adminRoutes)) {
            $this->loadRoutesFrom($adminRoutes);
        }
    }
}
