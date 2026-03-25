@extends('layouts.admin')

@section('title', 'Prompt-Bibliothek')
@section('breadcrumb', 'Prompt-Bibliothek')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Prompt-Bibliothek</h1>
                <p class="mt-1 text-sm text-slate-500">Wiederverwendbare KI-Prompts nach Anwendungsfall</p>
            </div>
            <a href="{{ route('admin.prompt-management.prompts.create') }}"
                class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                + Neuer Prompt
            </a>
        </div>

        <form method="get" action="{{ route('admin.prompt-management.prompts.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="use_case" class="mb-1 block text-xs font-medium text-slate-600">Anwendungsfall</label>
                <select id="use_case" name="use_case" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <option value="">Alle</option>
                    @foreach ($useCases as $case)
                        <option value="{{ $case->value }}" @selected((string) ($filterUseCase ?? '') === $case->value)>
                            {{ $case->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Titel</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Slug</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Anwendungsfall</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Aktiv</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($prompts as $prompt)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $prompt->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $prompt->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $prompt->use_case->label() }}</td>
                            <td class="px-4 py-3">{{ $prompt->is_active ? 'Ja' : 'Nein' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.prompt-management.prompts.edit', $prompt) }}"
                                    class="text-sky-600 hover:text-sky-800">Bearbeiten</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Noch keine Prompts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($prompts->hasPages())
            <div class="flex justify-end">{{ $prompts->links() }}</div>
        @endif
    </div>
@endsection
