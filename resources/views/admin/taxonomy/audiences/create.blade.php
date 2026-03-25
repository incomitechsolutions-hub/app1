@extends('layouts.admin')

@section('title', 'Zielgruppe anlegen')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Zielgruppe anlegen</h1>
            <a href="{{ route('admin.taxonomy.audiences.index') }}"
                class="text-sm font-medium text-slate-600 hover:text-slate-900">Zur Übersicht</a>
        </div>

        <form method="post" action="{{ route('admin.taxonomy.audiences.store') }}" class="admin-panel space-y-4 p-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-slate-700">Slug (optional)</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug') }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Beschreibung (optional)</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                class="inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
        </form>
    </div>
@endsection
