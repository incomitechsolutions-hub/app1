@extends('layouts.admin')

@section('title', 'Kurse')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Kurse</h1>
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-sm">
                    <a href="{{ route('admin.course-catalog.courses.index') }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ ! $trashed ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Aktiv
                    </a>
                    <a href="{{ route('admin.course-catalog.courses.index', ['trashed' => 1]) }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ $trashed ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Papierkorb
                    </a>
                </div>
                @if (! $trashed)
                    <a href="{{ route('admin.course-catalog.courses.create') }}"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Neuer Kurs
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Titel</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Slug</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Primärkategorie</th>
                        @if ($trashed)
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Entfernt am</th>
                        @endif
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($courses as $course)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $course->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $course->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @switch($course->status->value)
                                    @case('draft')
                                        Entwurf
                                        @break
                                    @case('review')
                                        Review
                                        @break
                                    @case('seo_review')
                                        SEO-Review
                                        @break
                                    @case('published')
                                        Veröffentlicht
                                        @break
                                    @default
                                        {{ $course->status->value }}
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $course->primaryCategory?->name ?? '—' }}</td>
                            @if ($trashed)
                                <td class="px-4 py-3 text-slate-600">{{ $course->deleted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.course-catalog.courses.show', $course) }}"
                                    class="font-medium text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-slate-900">Anzeigen</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $trashed ? 6 : 5 }}" class="px-4 py-8 text-center text-slate-500">
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

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            {{ $courses->links() }}
        </div>
    </div>
@endsection
