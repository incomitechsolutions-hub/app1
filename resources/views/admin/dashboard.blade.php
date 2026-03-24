@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <header>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard!</h1>
            <p class="text-slate-500">
                Willkommen zurück! Hier ist Ihr Überblick.
            </p>
        </header>

        @php
            $c = $counts;
        @endphp

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <a href="{{ route('admin.course-catalog.courses.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Kurse</p>
                        <p class="text-3xl font-bold text-slate-800">{{ $c['courses'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-sky-600" aria-hidden="true">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Hauptkategorien</p>
                        <p class="text-3xl font-bold text-slate-800">{{ $c['main_categories'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" aria-hidden="true">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Unterkategorien</p>
                        <p class="text-3xl font-bold text-slate-800">{{ $c['sub_categories'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-600" aria-hidden="true">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.inquiries.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Anfragen</p>
                        <p class="text-3xl font-bold text-slate-800">{{ $c['inquiries'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100 text-cyan-600" aria-hidden="true">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.pages.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Seiten</p>
                        <p class="text-3xl font-bold text-slate-800">{{ $c['pages'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-600" aria-hidden="true">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V12a9 9 0 0 0-9-9Z" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.course-catalog.courses.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-sm"
                title="Veröffentlichte Kurse im Katalog">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Empfohlen</p>
                        <p class="text-3xl font-bold text-slate-800">{{ $c['published_courses'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600" aria-hidden="true">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Letzte Aktivitäten</h2>
            @if (count($recent_activity) === 0)
                <p class="text-sm text-slate-500">
                    Noch keine Einträge. Sobald Kontaktanfragen eingehen, erscheinen sie hier.
                </p>
            @else
                <ul class="mt-4 divide-y divide-slate-100" role="list">
                    @foreach ($recent_activity as $item)
                        <li class="flex gap-4 py-4 first:pt-0">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500"
                                aria-hidden="true">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    <time datetime="{{ $item['at']->toIso8601String() }}">
                                        {{ $item['at']->format('d.m.Y') }} · {{ $item['at']->format('H:i') }}
                                    </time>
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
