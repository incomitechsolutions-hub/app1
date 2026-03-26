@php
    $params = request()->query();
    $sortUrl = function (string $column) use ($params, $sort, $order): string {
        $nextOrder = $sort === $column && $order === 'asc' ? 'desc' : 'asc';

        return route('admin.taxonomy.categories.index', array_merge($params, ['sort' => $column, 'order' => $nextOrder]));
    };
    $statusLabel = function (string $status): string {
        return match ($status) {
            'draft' => 'Entwurf',
            'published' => 'Veröffentlicht',
            'archived' => 'Archiviert',
            default => $status,
        };
    };
    $statusBadgeClass = function (string $status): string {
        return match ($status) {
            'published' => 'bg-emerald-100 text-emerald-700',
            'archived' => 'bg-slate-200 text-slate-600',
            default => 'bg-amber-100 text-amber-700',
        };
    };
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <div class="grid grid-cols-3 gap-2 sm:gap-4">
        <a href="{{ route('admin.taxonomy.categories.index', ['level' => 'all']) }}"
            class="js-category-ajax flex min-w-0 items-center gap-2 rounded-xl border bg-white p-3 shadow-sm transition hover:border-sky-200 hover:shadow sm:gap-3 sm:p-4 {{ $level === 'all' ? 'border-sky-300' : 'border-slate-200' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-600 sm:h-12 sm:w-12" aria-hidden="true">
                <svg class="h-5 w-5 sm:h-6 sm:w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0A2.25 2.25 0 0 0 3.75 9v.878m13.5-3A2.25 2.25 0 0 1 20.25 9v.878m-16.5 0a2.25 2.25 0 0 0-1.5 2.122V18a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18v-6.002a2.25 2.25 0 0 0-1.5-2.122m-16.5 0V9" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-xs">Gesamt</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-slate-900 sm:text-2xl">{{ $stats['all'] }}</p>
            </div>
        </a>
        <a href="{{ route('admin.taxonomy.categories.index', ['level' => 'root']) }}"
            class="js-category-ajax flex min-w-0 items-center gap-2 rounded-xl border bg-white p-3 shadow-sm transition hover:border-sky-200 hover:shadow sm:gap-3 sm:p-4 {{ $level === 'root' ? 'border-sky-300' : 'border-slate-200' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-500 sm:h-12 sm:w-12" aria-hidden="true">
                <svg class="h-5 w-5 sm:h-6 sm:w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-xs">Hauptkategorien</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-slate-900 sm:text-2xl">{{ $stats['root'] }}</p>
            </div>
        </a>
        <a href="{{ route('admin.taxonomy.categories.index', ['level' => 'child']) }}"
            class="js-category-ajax flex min-w-0 items-center gap-2 rounded-xl border bg-white p-3 shadow-sm transition hover:border-sky-200 hover:shadow sm:gap-3 sm:p-4 {{ $level === 'child' ? 'border-sky-300' : 'border-slate-200' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 sm:h-12 sm:w-12" aria-hidden="true">
                <svg class="h-5 w-5 sm:h-6 sm:w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-xs">Unterkategorien</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-slate-900 sm:text-2xl">{{ $stats['child'] }}</p>
            </div>
        </a>
    </div>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Kategorien</h1>
            <p class="mt-1 text-sm text-slate-500">Hierarchische Verwaltung aller Kurskategorien (beliebig viele Ebenen)</p>
        </div>
        <div class="inline-flex items-center gap-2">
            <a href="{{ route('admin.taxonomy.categories.import') }}"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                CSV-Import
            </a>
            <a href="{{ route('admin.taxonomy.categories.create') }}"
                class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                Neue Kategorie
            </a>
        </div>
    </div>

    <form method="get" action="{{ route('admin.taxonomy.categories.index') }}"
        class="admin-panel flex flex-wrap items-center gap-3 p-4">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <div class="min-w-[200px]">
            <label class="sr-only" for="filter-level">Ebenen</label>
            <select id="filter-level" name="level"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="all" @selected($level === 'all')>Alle Ebenen (Baum)</option>
                <option value="root" @selected($level === 'root')>Nur Hauptkategorien</option>
                <option value="child" @selected($level === 'child')>Nur Unterkategorien</option>
            </select>
        </div>
        <div class="min-w-[240px] flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Suchen (Name, Slug, Beschreibung)..."
                autocomplete="off"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>
        <select name="status"
            class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            <option value="">Alle Status</option>
            <option value="draft" @selected($status === 'draft')>Entwurf</option>
            <option value="published" @selected($status === 'published')>Veröffentlicht</option>
            <option value="archived" @selected($status === 'archived')>Archiviert</option>
        </select>
        <select name="order"
            class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            <option value="asc" @selected($order === 'asc')>Aufsteigend</option>
            <option value="desc" @selected($order === 'desc')>Absteigend</option>
        </select>
        <span class="sr-only">Filter werden automatisch angewendet.</span>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <form id="category-bulk-form" method="post" action="{{ route('admin.taxonomy.categories.bulk-update') }}"
            class="flex flex-wrap items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3">
            @csrf
            <input type="hidden" name="level" value="{{ $level }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="order" value="{{ $order }}">
            @if ($status !== '')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            @if ($search !== '')
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <input type="hidden" name="action" value="set_status">
            <span class="text-sm font-medium text-slate-700">Mehrfachaktion</span>
            <label class="sr-only" for="bulk-status">Neuer Status</label>
            <select id="bulk-status" name="bulk_status"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="draft">Entwurf</option>
                <option value="published" selected>Veröffentlicht</option>
                <option value="archived">Archiviert</option>
            </select>
            <button type="submit"
                class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                Anwenden
            </button>
        </form>
        <table class="min-w-full divide-y divide-slate-200 text-sm"
            data-category-reorder-url="{{ route('admin.taxonomy.categories.reorder') }}"
            data-drag-enabled="{{ $level === 'all' ? '1' : '0' }}">
            <thead class="bg-slate-50/80">
                <tr>
                    <th class="w-10 px-2 py-3 text-left font-medium text-slate-700" scope="col">
                        <input type="checkbox" form="category-bulk-form"
                            class="js-category-select-all rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                            data-row-action aria-label="Alle sichtbaren Kategorien auswählen">
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">
                        <a href="{{ $sortUrl('id') }}" class="js-category-ajax inline-flex items-center gap-1 hover:text-slate-900">
                            ID
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">
                        <a href="{{ $sortUrl('name') }}" class="js-category-ajax inline-flex items-center gap-1 hover:text-slate-900">
                            Name
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">
                        <a href="{{ $sortUrl('slug') }}" class="js-category-ajax inline-flex items-center gap-1 hover:text-slate-900">
                            Slug
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">Parent</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">
                        <a href="{{ $sortUrl('children_count') }}" class="js-category-ajax inline-flex items-center gap-1 hover:text-slate-900">
                            Kinder
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">
                        <a href="{{ $sortUrl('courses_count') }}" class="js-category-ajax inline-flex items-center gap-1 hover:text-slate-900">
                            Kurse
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-slate-700">
                        <a href="{{ $sortUrl('status') }}" class="js-category-ajax inline-flex items-center gap-1 hover:text-slate-900">
                            Status
                        </a>
                    </th>
                    <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($treeRows as $row)
                    @php
                        $category = $row->category;
                        $depth = $row->depth;
                    @endphp
                    <tr @class([
                        'cursor-pointer transition hover:bg-slate-50/70',
                        'bg-slate-50/60' => $depth > 0,
                    ])
                        draggable="false"
                        data-category-id="{{ $category->id }}"
                        data-parent-id="{{ $category->parent_id ?? '' }}"
                        data-depth="{{ $depth }}"
                        data-edit-url="{{ route('admin.taxonomy.categories.edit', $category) }}"
                        title="Zeile anklicken zum Bearbeiten">
                        <td class="px-2 py-3 align-middle" data-row-action>
                            <input type="checkbox" form="category-bulk-form" name="ids[]" value="{{ $category->id }}"
                                class="js-category-row-checkbox rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                data-row-action aria-label="Kategorie {{ $category->name }} auswählen">
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $category->id }}</td>
                        <td class="px-4 py-3" data-tree-cell>
                            <div class="flex min-w-0 items-start gap-2">
                                <button type="button"
                                    class="inline-flex h-7 w-7 shrink-0 cursor-grab items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 active:cursor-grabbing"
                                    title="Ziehen zum Verschieben"
                                    data-row-action
                                    data-drag-handle>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M7 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7 10a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7 16a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM15 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM15 10a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM15 16a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/>
                                    </svg>
                                </button>
                                @if ($depth > 0)
                                    <div
                                        class="flex shrink-0 items-center gap-1 border-l-2 border-slate-200 pl-2"
                                        style="margin-left: {{ max(0, ($depth - 1) * 24) }}px">
                                        <svg class="h-3 w-3 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                    @if ($category->parent_id)
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M6 6.75A.75.75 0 0 1 6.75 6h6.5a.75.75 0 0 1 0 1.5H7.5v5.75A.75.75 0 0 1 6 13.25V6.75Z" />
                                            <path d="M12.25 10.5a.75.75 0 0 1 1.06 0l2 2a.75.75 0 0 1 0 1.06l-2 2a.75.75 0 1 1-1.06-1.06L13.72 13l-1.47-1.44a.75.75 0 0 1 0-1.06Z" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M2 5.25A2.25 2.25 0 0 1 4.25 3h3.568a2.25 2.25 0 0 1 1.591.659l.932.932a.75.75 0 0 0 .53.219h5.879A2.25 2.25 0 0 1 19 7.06v7.69A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Z" />
                                        </svg>
                                    @endif
                                </span>
                                <span @class([
                                    'min-w-0 font-medium',
                                    'text-slate-900' => $depth === 0,
                                    'text-slate-700' => $depth > 0,
                                ])>{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->slug }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->parent?->name ?? 'Hauptkategorie' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->children_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $category->courses_count }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadgeClass($category->status) }}">
                                {{ $statusLabel($category->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right" data-row-action>
                            <div class="inline-flex flex-nowrap items-center justify-end gap-2 whitespace-nowrap">
                                <a href="{{ route('admin.taxonomy.categories.create', ['parent_id' => $category->id]) }}"
                                    data-row-action
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                    title="Unterkategorie anlegen">
                                    <span class="sr-only">Kind hinzufügen</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.taxonomy.categories.edit', $category) }}"
                                    data-row-action
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                                    title="Bearbeiten">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>
                                <form method="post" action="{{ route('admin.taxonomy.categories.destroy', $category) }}" class="inline" data-row-action>
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        data-category-delete
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100"
                                        title="Löschen">
                                        <span class="sr-only">Löschen</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">Keine Kategorien für den aktuellen Filter gefunden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center gap-6 text-sm text-slate-500">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2 5.25A2.25 2.25 0 0 1 4.25 3h3.568a2.25 2.25 0 0 1 1.591.659l.932.932a.75.75 0 0 0 .53.219h5.879A2.25 2.25 0 0 1 19 7.06v7.69A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Z" />
                </svg>
            </span>
            <span>Hauptkategorie (Ebene 1)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6 6.75A.75.75 0 0 1 6.75 6h6.5a.75.75 0 0 1 0 1.5H7.5v5.75A.75.75 0 0 1 6 13.25V6.75Z" />
                    <path d="M12.25 10.5a.75.75 0 0 1 1.06 0l2 2a.75.75 0 0 1 0 1.06l-2 2a.75.75 0 1 1-1.06-1.06L13.72 13l-1.47-1.44a.75.75 0 0 1 0-1.06Z" />
                </svg>
            </span>
            <span>Unterkategorien (weitere Ebenen)</span>
        </div>
    </div>
</div>
