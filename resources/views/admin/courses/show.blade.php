@extends('layouts.admin')

@section('title', $course->title)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $course->title }}</h1>
            <p class="mt-1 text-sm text-slate-600">Slug: {{ $course->slug }} · Status: {{ $course->status->value }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.course-catalog.courses.index') }}"
                class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">Übersicht</a>
            <a href="{{ route('admin.course-catalog.courses.edit', $course) }}"
                class="rounded bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Bearbeiten</a>
            <form method="post" action="{{ route('admin.course-catalog.courses.destroy', $course) }}" class="inline"
                onsubmit="return confirm('Kurs wirklich entfernen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 hover:bg-red-100">
                    Löschen
                </button>
            </form>
        </div>
    </div>

    <dl class="mt-8 grid gap-6 sm:grid-cols-2">
        <div>
            <dt class="text-sm font-medium text-slate-500">Kurzbeschreibung</dt>
            <dd class="mt-1 text-sm text-slate-900 whitespace-pre-wrap">{{ $course->short_description ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-slate-500">Sprache</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $course->language_code }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-slate-500">Dauer (h)</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $course->duration_hours ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-slate-500">Veröffentlicht am</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $course->published_at?->format('Y-m-d H:i') ?? '—' }}</dd>
        </div>
    </dl>

    <div class="mt-8">
        <h2 class="text-lg font-medium text-slate-900">Kategorien</h2>
        <ul class="mt-2 list-inside list-disc text-sm text-slate-800">
            @forelse ($course->categories as $cat)
                <li>{{ $cat->name }} @if ($course->primary_category_id === $cat->id) (primär) @endif</li>
            @empty
                <li class="text-slate-500">Keine</li>
            @endforelse
        </ul>
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
        <div class="mt-2 max-w-none text-sm text-slate-800 whitespace-pre-wrap">{{ $course->long_description ?: '—' }}</div>
    </div>
@endsection
