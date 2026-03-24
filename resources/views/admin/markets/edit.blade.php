@extends('layouts.admin')

@section('title', 'Markt bearbeiten')
@section('breadcrumb', 'Länder')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <h1 class="text-3xl font-bold text-slate-900">Markt bearbeiten</h1>
        <form method="post" action="{{ route('admin.localization.markets.update', $market) }}" class="admin-panel space-y-4 p-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700">Anzeigename</label>
                <input type="text" name="name" value="{{ old('name', $market->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Code (Badge)</label>
                    <input type="text" name="display_code" value="{{ old('display_code', $market->display_code) }}" required maxlength="8" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    @error('display_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">ISO-Land (optional)</label>
                    <input type="text" name="country_code" value="{{ old('country_code', $market->country_code) }}" maxlength="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm uppercase">
                    @error('country_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Domain</label>
                <input type="text" name="domain" value="{{ old('domain', $market->domain) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                @error('domain')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">MwSt (%)</label>
                <input type="number" name="vat_rate" value="{{ old('vat_rate', $market->vat_rate) }}" step="0.01" min="0" max="100" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                @error('vat_rate')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Standard-Sprache</label>
                <select name="default_locale_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">—</option>
                    @foreach ($locales as $loc)
                        <option value="{{ $loc->id }}" @selected(old('default_locale_id', $market->default_locale_id) == $loc->id)>{{ $loc->name }} ({{ $loc->code }})</option>
                    @endforeach
                </select>
                @error('default_locale_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Sortierung</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $market->sort_order) }}" min="0" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $market->is_active))>
                Aktiv
            </label>
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
                <a href="{{ route('admin.localization.markets.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Zurück</a>
            </div>
        </form>
    </div>
@endsection
