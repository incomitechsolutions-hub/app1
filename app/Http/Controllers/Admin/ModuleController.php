<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ModuleRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\View\View;
use RuntimeException;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleRegistry $modules
    ) {}

    public function index(): View
    {
        $this->authorize('manage-modules');

        return view('admin.modules.index', [
            'modules' => $this->modules->allForOverview(),
        ]);
    }

    public function update(Request $request, string $moduleKey): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-modules');

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        try {
            $enabled = (bool) $validated['enabled'];
            $this->modules->setEnabled($moduleKey, $enabled);
        } catch (InvalidArgumentException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Unbekannter Modul-Schlüssel.'),
                ], 422);
            }

            return redirect()
                ->route('admin.modules.index')
                ->with('status', __('Unbekannter Modul-Schlüssel.'));
        } catch (RuntimeException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Modulstatus kann aktuell nicht gespeichert werden. Bitte Migrationen ausführen.'),
                ], 503);
            }

            return redirect()
                ->route('admin.modules.index')
                ->with('status', __('Modulstatus kann aktuell nicht gespeichert werden. Bitte Migrationen ausführen.'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'module_key' => $moduleKey,
                'enabled' => $enabled,
                'status_label' => $enabled ? 'Aktiv' : 'Inaktiv',
                'message' => __('Modulstatus wurde aktualisiert.'),
            ]);
        }

        return redirect()
            ->route('admin.modules.index')
            ->with('status', __('Modulstatus wurde aktualisiert.'));
    }
}
