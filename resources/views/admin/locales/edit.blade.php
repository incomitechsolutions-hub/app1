@extends('layouts.admin')

@section('title', 'Sprache bearbeiten')
@section('breadcrumb', 'Sprachen')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <h1 class="text-3xl font-bold text-slate-900">Sprache bearbeiten</h1>
        <form method="post" action="{{ route('admin.localization.locales.update', $locale) }}" class="admin-panel space-y-4 p-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700">Code</label>
                <input type="text" name="code" value="{{ old('code', $locale->code) }}" required maxlength="16" class="mt-1 w-full rounded-lg border-slate-300 text-sm font-mono">
                @error('code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $locale->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Sortierung</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $locale->sort_order) }}" min="0" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $locale->is_active))>
                Aktiv
            </label>
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
                <a href="{{ route('admin.localization.locales.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Zurück</a>
            </div>
        </form>
    </div>
@endsection
