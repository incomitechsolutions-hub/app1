<?php

namespace App\Domain\Localization\Http\Controllers\Admin;

use App\Domain\Localization\Http\Requests\Admin\StoreMarketRequest;
use App\Domain\Localization\Http\Requests\Admin\UpdateMarketRequest;
use App\Domain\Localization\Models\Locale;
use App\Domain\Localization\Models\Market;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('filter_status', '');

        $query = Market::query()->orderBy('sort_order')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('display_code', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%");
            });
        }

        if ($status === '1') {
            $query->where('is_active', true);
        } elseif ($status === '0') {
            $query->where('is_active', false);
        }

        $markets = $query->paginate(20)->withQueryString();

        $total = Market::query()->count();
        $active = Market::query()->where('is_active', true)->count();
        $inactive = Market::query()->where('is_active', false)->count();

        return view('admin.markets.index', [
            'markets' => $markets,
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
        return view('admin.markets.create', [
            'locales' => Locale::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMarketRequest $request): RedirectResponse
    {
        Market::query()->create($request->validated());

        return redirect()
            ->route('admin.localization.markets.index')
            ->with('status', __('Markt wurde angelegt.'));
    }

    public function edit(Market $market): View
    {
        return view('admin.markets.edit', [
            'market' => $market,
            'locales' => Locale::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateMarketRequest $request, Market $market): RedirectResponse
    {
        $market->update($request->validated());

        return redirect()
            ->route('admin.localization.markets.index')
            ->with('status', __('Markt wurde aktualisiert.'));
    }

    public function destroy(Market $market): RedirectResponse
    {
        $market->delete();

        return redirect()
            ->route('admin.localization.markets.index')
            ->with('status', __('Markt wurde gelöscht.'));
    }
}
