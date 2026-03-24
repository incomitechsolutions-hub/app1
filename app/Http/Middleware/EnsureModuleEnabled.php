<?php

namespace App\Http\Middleware;

use App\Services\Admin\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private readonly ModuleRegistry $modules
    ) {}

    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (! $this->modules->isEnabled($moduleKey)) {
            abort(403, __('Dieses Modul ist deaktiviert.'));
        }

        return $next($request);
    }
}
