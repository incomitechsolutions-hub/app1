@extends('layouts.admin')

@section('title', 'Programme')
@section('breadcrumb', 'Programme')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Programme</h1>
                <p class="mt-1 text-sm text-slate-500">Kurspakete und Ausbildungspfade verwalten</p>
            </div>
            <a href="{{ route('admin.course-catalog.programs.create') }}"
                class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                + Neues Programm
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Titel</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Slug</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($programs as $program)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $program->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $program->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $program->status }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.course-catalog.programs.edit', $program) }}"
                                    class="text-sky-600 hover:text-sky-800">Bearbeiten</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Noch keine Programme angelegt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($programs->hasPages())
            <div class="flex justify-end">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
@endsection
