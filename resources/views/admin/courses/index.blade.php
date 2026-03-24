@extends('layouts.admin')

@section('title', 'Kurse')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Kurse</h1>
            <a href="{{ route('admin.course-catalog.courses.create') }}"
                class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                Neuer Kurs
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Titel</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Slug</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Primärkategorie</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($courses as $course)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $course->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $course->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $course->status->value }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $course->primaryCategory?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.course-catalog.courses.show', $course) }}"
                                    class="font-medium text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-slate-900">Anzeigen</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Noch keine Kurse.</td>
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
