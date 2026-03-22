@extends('layouts.admin')

@section('title', 'Kurse')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900">Kurse</h1>
        <a href="{{ route('admin.course-catalog.courses.create') }}"
            class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            Neuer Kurs
        </a>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
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
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $course->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $course->slug }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $course->status->value }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $course->primaryCategory?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.course-catalog.courses.show', $course) }}"
                                class="text-slate-700 underline hover:text-slate-900">Anzeigen</a>
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

    <div class="mt-4">
        {{ $courses->links() }}
    </div>
@endsection
