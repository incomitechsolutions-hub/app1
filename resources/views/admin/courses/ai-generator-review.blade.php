@extends('layouts.admin')

@section('title', 'KI: Entwurf prüfen')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Entwurf prüfen</h1>
            <p class="mt-1 text-sm text-slate-500">Passen Sie die Felder an und legen Sie den Kurs als Entwurf an.</p>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.courses.ai-generator.store') }}" class="admin-panel space-y-6 p-6">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700">Titel</label>
                <input id="title" name="title" type="text" required value="{{ old('title', $draft['title'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-slate-700">Slug</label>
                <input id="slug" name="slug" type="text" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                    value="{{ old('slug', $draft['slug'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="short_description" class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
                <textarea id="short_description" name="short_description" rows="3" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('short_description', $draft['short_description'] ?? '') }}</textarea>
                @error('short_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="long_description" class="block text-sm font-medium text-slate-700">Langtext</label>
                <textarea id="long_description" name="long_description" rows="8"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('long_description', $draft['long_description'] ?? '') }}</textarea>
                @error('long_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="duration_days" class="block text-sm font-medium text-slate-700">Dauer (Tage)</label>
                    <input id="duration_days" name="duration_days" type="number" min="0"
                        value="{{ old('duration_days', $draft['duration_days'] ?? '') }}"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    @error('duration_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-slate-700">Preis</label>
                    <input id="price" name="price" type="text" inputmode="decimal"
                        value="{{ old('price', $draft['price'] ?? '') }}"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="language_code" class="block text-sm font-medium text-slate-700">Sprache</label>
                <input id="language_code" name="language_code" type="text" maxlength="16" required
                    value="{{ old('language_code', $draft['language_code'] ?? 'de') }}"
                    class="mt-1 block w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('language_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="primary_category_id" class="block text-sm font-medium text-slate-700">Kategorie (optional)</label>
                <select id="primary_category_id" name="primary_category_id"
                    class="mt-1 block w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    <option value="">—</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('primary_category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('primary_category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="seo_title" class="block text-sm font-medium text-slate-700">SEO-Titel</label>
                <input id="seo_title" name="seo[seo_title]" type="text" maxlength="255"
                    value="{{ old('seo.seo_title', $draft['seo_title'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('seo.seo_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="meta_description" class="block text-sm font-medium text-slate-700">Meta-Beschreibung</label>
                <textarea id="meta_description" name="seo[meta_description]" rows="3" maxlength="1000"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('seo.meta_description', $draft['meta_description'] ?? '') }}</textarea>
                @error('seo.meta_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Kurs anlegen (Entwurf)
                </button>
                <a href="{{ route('admin.course-catalog.courses.ai-generator') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    Zurück
                </a>
            </div>
        </form>
    </div>
@endsection
