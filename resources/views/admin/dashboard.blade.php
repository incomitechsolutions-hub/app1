@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600">
            Willkommen, {{ auth()->user()->name }}. Alle Bereiche erreichst du über die Navigation links (auf dem Handy unter „Menü“).
        </p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('admin.course-catalog.courses.index') }}"
            class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300">
            <h2 class="font-medium text-slate-900">Kurse</h2>
            <p class="mt-1 text-sm text-slate-600">Katalog verwalten</p>
        </a>
        <a href="{{ route('admin.taxonomy.categories.index') }}"
            class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300">
            <h2 class="font-medium text-slate-900">Kategorien</h2>
            <p class="mt-1 text-sm text-slate-600">Taxonomie (Platzhalter)</p>
        </a>
        <a href="{{ route('admin.inquiries.index') }}"
            class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300">
            <h2 class="font-medium text-slate-900">Anfragen</h2>
            <p class="mt-1 text-sm text-slate-600">Leads (Platzhalter)</p>
        </a>
    </div>
@endsection
