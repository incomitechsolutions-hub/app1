@extends('layouts.admin')

@section('title', $course->title)
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        @if ($course->trashed())
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                Dieser Kurs liegt im <strong>Papierkorb</strong> und ist im öffentlichen Katalog nicht sichtbar.
            </div>
        @endif

        <div class="admin-panel p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $course->title }}</h1>
                    <p class="mt-1 text-sm text-slate-600">Slug: {{ $course->slug }} · Status: {{ $course->status->value }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.course-catalog.courses.index') }}"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">Übersicht</a>
                    @if ($course->trashed())
                        <form method="post" action="{{ route('admin.course-catalog.courses.restore', $course) }}" class="inline">
                            @csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                Wiederherstellen
                            </button>
                        </form>
                    @else
                        <a href="{{ route('admin.course-catalog.courses.edit', $course) }}"
                            class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Bearbeiten</a>
                        <form method="post" action="{{ route('admin.course-catalog.courses.destroy', $course) }}" class="inline"
                            onsubmit="return confirm('Kurs in den Papierkorb legen? Er kann später wiederhergestellt werden.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 hover:bg-red-100">
                                In Papierkorb legen
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-panel p-6">
            <dl class="grid gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-slate-500">Kurzbeschreibung</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $course->short_description ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Sprache</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $course->language_code }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Dauer (Stunden)</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ $course->duration_hours !== null ? number_format((float) $course->duration_hours, 1, ',', '.') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Preis (EUR)</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $course->price !== null ? number_format((float) $course->price, 2, ',', '.').' €' : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Format</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $course->delivery_format?->value ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Featured</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $course->is_featured ? 'Ja' : 'Nein' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Buchungslink</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        @if ($course->booking_url)
                            <a href="{{ $course->booking_url }}" class="text-sky-600 underline" target="_blank" rel="noopener">{{ $course->booking_url }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Angebotslink</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        @if ($course->offer_url)
                            <a href="{{ $course->offer_url }}" class="text-sky-600 underline" target="_blank" rel="noopener">{{ $course->offer_url }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Veröffentlicht am</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $course->published_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
            </dl>

            <div class="mt-8">
                <h2 class="text-lg font-medium text-slate-900">Kategorie</h2>
                <p class="mt-2 text-sm text-slate-800">
                    {{ $course->primaryCategory?->name ?? '—' }}
                </p>
            </div>

            @if ($course->tags->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-lg font-medium text-slate-900">Tags</h2>
                    <p class="mt-2 text-sm text-slate-800">{{ $course->tags->pluck('name')->join(', ') }}</p>
                </div>
            @endif

            @if ($course->audiences->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-lg font-medium text-slate-900">Zielgruppen</h2>
                    <p class="mt-2 text-sm text-slate-800">{{ $course->audiences->pluck('name')->join(', ') }}</p>
                </div>
            @endif

            @if ($course->modules->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-slate-900">Module</h2>
                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-slate-800">
                        @foreach ($course->modules as $m)
                            <li>
                                <span class="font-medium">{{ $m->title }}</span>
                                @if ($m->description)
                                    <div class="text-slate-600">{{ $m->description }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            @if ($course->learningObjectives->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-slate-900">Lernziele</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-800">
                        @foreach ($course->learningObjectives as $o)
                            <li>{{ $o->objective_text }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($course->prerequisites->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-slate-900">Voraussetzungen</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-800">
                        @foreach ($course->prerequisites as $p)
                            <li>{{ $p->prerequisite_text }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8">
                <h2 class="text-lg font-medium text-slate-900">Langtext</h2>
                <div class="mt-2 max-w-none whitespace-pre-wrap text-sm text-slate-800">{{ $course->long_description ?: '—' }}</div>
            </div>
        </div>
    </div>
@endsection
