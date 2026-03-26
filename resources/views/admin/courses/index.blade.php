@php
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;

    $filterQuery = array_filter([
        'q' => $filters['q'] ?? null,
        'category_id' => $filters['category_id'] ?? null,
        'difficulty_level_id' => $filters['difficulty_level_id'] ?? null,
        'delivery_format' => $filters['delivery_format'] ?? null,
        'status' => $filters['status'] ?? null,
        'featured' => $featuredFilter ? '1' : null,
        'trashed' => $trashed ? '1' : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

@extends('layouts.admin')

@section('title', 'Kurse')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6" data-course-index-root>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Kurse</h1>
                <p class="mt-1 text-sm text-slate-500">Verwalten Sie Ihre Kurse</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="get" action="{{ route('admin.course-catalog.courses.index') }}" class="flex flex-wrap items-center gap-2">
                    @foreach (['trashed', 'featured'] as $k)
                        @if (! empty($filterQuery[$k]))
                            <input type="hidden" name="{{ $k }}" value="{{ $filterQuery[$k] }}">
                        @endif
                    @endforeach
                    <label class="sr-only" for="q">Suchen</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Suchen…"
                        class="w-48 min-w-[12rem] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 sm:w-56">
                    <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Suchen</button>
                </form>
                <a href="{{ route('admin.course-catalog.settings.edit') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.37.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.217.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    Einstellungen
                </a>
                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-sm">
                    <a href="{{ route('admin.course-catalog.courses.index') }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ ! $trashed ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Aktiv
                    </a>
                    <a href="{{ route('admin.course-catalog.courses.index', array_merge($filterQuery, ['trashed' => 1])) }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ $trashed ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Papierkorb
                    </a>
                </div>
                @if (! $trashed)
                    <a href="{{ $featuredFilter ? route('admin.course-catalog.courses.index', $filterQuery) : route('admin.course-catalog.courses.index', array_merge($filterQuery, ['featured' => 1])) }}"
                        class="inline-flex items-center rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $featuredFilter ? 'border-sky-600 bg-sky-50 text-sky-900' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                        {{ $featuredFilter ? 'Alle Kurse' : 'Nur empfohlen' }}
                    </a>
                    <a href="{{ route('admin.course-catalog.courses.create') }}"
                        class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                        + Neuer Kurs
                    </a>
                @endif
            </div>
        </div>

        <form method="get" action="{{ route('admin.course-catalog.courses.index') }}" class="flex flex-wrap items-end justify-end gap-3">
            @foreach (['q', 'trashed', 'featured'] as $k)
                @if (! empty($filterQuery[$k]))
                    <input type="hidden" name="{{ $k }}" value="{{ $filterQuery[$k] }}">
                @endif
            @endforeach
            <div>
                <label for="category_id" class="mb-1 block text-xs font-medium text-slate-600">Kategorie</label>
                <select id="category_id" name="category_id" onchange="this.form.submit()"
                    class="min-w-[10rem] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <option value="">Alle Kategorien</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="difficulty_level_id" class="mb-1 block text-xs font-medium text-slate-600">Level</label>
                <select id="difficulty_level_id" name="difficulty_level_id" onchange="this.form.submit()"
                    class="min-w-[9rem] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <option value="">Alle Level</option>
                    @foreach ($difficultyLevels as $lvl)
                        <option value="{{ $lvl->id }}" @selected((string) ($filters['difficulty_level_id'] ?? '') === (string) $lvl->id)>{{ $lvl->label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="delivery_format" class="mb-1 block text-xs font-medium text-slate-600">Format</label>
                <select id="delivery_format" name="delivery_format" onchange="this.form.submit()"
                    class="min-w-[9rem] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <option value="">Alle Formate</option>
                    @foreach ($deliveryFormats as $fmt)
                        <option value="{{ $fmt->value }}" @selected(($filters['delivery_format'] ?? '') === $fmt->value)>
                            @switch($fmt)
                                @case(DeliveryFormat::Online) Online @break
                                @case(DeliveryFormat::Presence) Präsenz @break
                                @case(DeliveryFormat::Hybrid) Hybrid @break
                            @endswitch
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select id="status" name="status" onchange="this.form.submit()"
                    class="min-w-[9rem] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <option value="">Alle</option>
                    @foreach ($courseStatuses as $st)
                        <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->value }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">ID</th>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">Kurs-ID</th>
                            <th class="min-w-[14rem] px-3 py-3 text-left font-medium text-slate-700">Titel</th>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">Dauer</th>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">Preis</th>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">Kategorie</th>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">Level</th>
                            <th class="px-3 py-3 text-left font-medium text-slate-700">Empfohlen</th>
                            @if ($trashed)
                                <th class="px-3 py-3 text-left font-medium text-slate-700">Entfernt am</th>
                            @endif
                            <th class="px-3 py-3 text-right font-medium text-slate-700">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($courses as $course)
                            <tr
                                @class([
                                    'transition hover:bg-slate-50/70',
                                    'cursor-pointer' => ! $trashed,
                                ])
                                @if (! $trashed)
                                    data-edit-url="{{ route('admin.course-catalog.courses.edit', $course) }}"
                                    title="Zeile anklicken zum Bearbeiten"
                                @endif
                            >
                                <td class="px-3 py-3 tabular-nums text-slate-600">{{ $course->id }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-slate-700">{{ $course->external_course_code ?? '—' }}</td>
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-slate-900">{{ $course->title }}</p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @if ($course->delivery_format)
                                            <span class="inline-flex rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-800">
                                                @switch($course->delivery_format)
                                                    @case(DeliveryFormat::Online) Online @break
                                                    @case(DeliveryFormat::Presence) Präsenz @break
                                                    @case(DeliveryFormat::Hybrid) Hybrid @break
                                                @endswitch
                                            </span>
                                        @endif
                                        @if ($course->lessons_count)
                                            <span class="inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-900">{{ $course->lessons_count }} Lektionen</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-slate-700">
                                    @if ($course->duration_hours !== null)
                                        {{ number_format((float) $course->duration_hours, 1, ',', '.') }} Std.
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-slate-800">
                                    @if ($course->price !== null)
                                        {{ number_format((float) $course->price, 2, ',', '.') }} {{ $course->currency_code }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-slate-700">{{ $course->primaryCategory?->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-slate-700">{{ $course->difficultyLevel?->label ?? '—' }}</td>
                                <td class="px-3 py-3">
                                    @if ($course->is_featured)
                                        <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800">Ja</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Nein</span>
                                    @endif
                                </td>
                                @if ($trashed)
                                    <td class="px-3 py-3 text-slate-600">{{ $course->deleted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                @endif
                                <td class="px-3 py-3 text-right" data-row-action>
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.course-catalog.courses.show', $course) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800" title="Anzeigen">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        @if (! $trashed)
                                            <a href="{{ route('admin.course-catalog.courses.edit', $course) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800" title="Bearbeiten">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </a>
                                            <form method="post" action="{{ route('admin.course-catalog.courses.destroy', $course) }}" class="inline"
                                                onsubmit="return confirm('Kurs wirklich löschen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Löschen">
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $trashed ? 10 : 9 }}" class="px-4 py-8 text-center text-slate-500">
                                    @if ($trashed)
                                        Papierkorb ist leer.
                                    @else
                                        Noch keine Kurse.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            <p>
                @if ($courses->total() > 0)
                    Zeige {{ $courses->firstItem() }} bis {{ $courses->lastItem() }} von {{ $courses->total() }}
                @else
                    Keine Einträge
                @endif
            </p>
            <div>{{ $courses->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin-course-index.js'])
@endpush
