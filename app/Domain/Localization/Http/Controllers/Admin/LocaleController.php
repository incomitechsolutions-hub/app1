<?php

namespace App\Domain\Localization\Http\Controllers\Admin;

use App\Domain\Localization\Http\Requests\Admin\StoreLocaleRequest;
use App\Domain\Localization\Http\Requests\Admin\UpdateLocaleRequest;
use App\Domain\Localization\Models\Locale;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('filter_status', '');

        $query = Locale::query()->orderBy('sort_order')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status === '1') {
            $query->where('is_active', true);
        } elseif ($status === '0') {
            $query->where('is_active', false);
        }

        $locales = $query->paginate(20)->withQueryString();

        $total = Locale::query()->count();
        $active = Locale::query()->where('is_active', true)->count();
        $inactive = Locale::query()->where('is_active', false)->count();

        return view('admin.locales.index', [
            'locales' => $locales,
            'search' => $search,
            'filter_status' => $status,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.locales.create');
    }

    public function store(StoreLocaleRequest $request): RedirectResponse
    {
        Locale::query()->create($request->validated());

        return redirect()
            ->route('admin.localization.locales.index')
            ->with('status', __('Sprache wurde angelegt.'));
    }

    public function edit(Locale $locale): View
    {
        return view('admin.locales.edit', [
            'locale' => $locale,
        ]);
    }

    public function update(UpdateLocaleRequest $request, Locale $locale): RedirectResponse
    {
        $locale->update($request->validated());

        return redirect()
            ->route('admin.localization.locales.index')
            ->with('status', __('Sprache wurde aktualisiert.'));
    }

    public function destroy(Locale $locale): RedirectResponse
    {
        if ($locale->markets()->exists()) {
            return redirect()
                ->route('admin.localization.locales.index')
                ->with('status', __('Sprache kann nicht gelöscht werden, solange sie als Standard-Locale eines Marktes verwendet wird.'));
        }

        $locale->delete();

        return redirect()
            ->route('admin.localization.locales.index')
            ->with('status', __('Sprache wurde gelöscht.'));
    }
}
