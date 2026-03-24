@extends('layouts.admin')

@section('title', 'Module')
@section('breadcrumb', 'Module')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6" id="module-manager" data-csrf="{{ csrf_token() }}">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Extension Manager</h1>
                <p class="mt-1 text-sm text-slate-500">Manage installed and available extensions.</p>
            </div>
        </div>

        <div id="module-toast" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm" aria-label="Extension Manager">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Logo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Version</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($modules as $module)
                        <tr class="transition hover:bg-slate-50/70" data-module-row="{{ $module['key'] }}">
                            <td class="px-4 py-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                    <span class="text-base font-semibold">M</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $module['name'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $module['description'] !== '' ? $module['description'] : '—' }}</p>
                                <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $module['key'] }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-slate-700">v{{ $module['version'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span data-module-status-badge="{{ $module['key'] }}"
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $module['enabled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $module['enabled'] ? 'Aktiv' : 'Inaktiv' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button"
                                        class="rounded-md p-2 text-slate-400 transition hover:bg-sky-50 hover:text-sky-600"
                                        title="Info">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.25-4.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm.75 4.5a.75.75 0 0 0 0 1.5h.75v2.25a.75.75 0 0 0 1.5 0v-3a.75.75 0 0 0-.75-.75h-1.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <form method="post" action="{{ route('admin.modules.update', $module['key']) }}" data-module-toggle-form="{{ $module['key'] }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="enabled" data-module-enabled-input="{{ $module['key'] }}"
                                            value="{{ $module['enabled'] ? '0' : '1' }}">
                                        <button type="submit"
                                            data-module-toggle-btn="{{ $module['key'] }}"
                                            class="rounded-md p-2 transition {{ $module['enabled'] ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}"
                                            aria-label="{{ $module['enabled'] ? 'Modul deaktivieren' : 'Modul aktivieren' }}"
                                            title="{{ $module['enabled'] ? 'Deaktivieren' : 'Aktivieren' }}">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 3a9 9 0 1 0 9 9 1 1 0 1 0-2 0 7 7 0 1 1-7-7 1 1 0 1 0 0-2Z" />
                                                <path d="M12 7.5a4.5 4.5 0 1 0 4.5 4.5A4.5 4.5 0 0 0 12 7.5Z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('module-manager');
            if (!root) {
                return;
            }

            const csrf = root.dataset.csrf;
            const toast = document.getElementById('module-toast');

            function showToast(message, type) {
                if (!toast) {
                    return;
                }

                toast.textContent = message;
                toast.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800', 'border-red-200', 'bg-red-50', 'text-red-800');
                if (type === 'success') {
                    toast.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                } else {
                    toast.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
                }

                window.clearTimeout(window.__moduleToastTimer);
                window.__moduleToastTimer = window.setTimeout(() => {
                    toast.classList.add('hidden');
                }, 2600);
            }

            function paintState(key, enabled) {
                const badge = root.querySelector('[data-module-status-badge="' + key + '"]');
                const btn = root.querySelector('[data-module-toggle-btn="' + key + '"]');
                const input = root.querySelector('[data-module-enabled-input="' + key + '"]');

                if (badge) {
                    badge.textContent = enabled ? 'Aktiv' : 'Inaktiv';
                    badge.classList.toggle('bg-emerald-100', enabled);
                    badge.classList.toggle('text-emerald-700', enabled);
                    badge.classList.toggle('bg-slate-100', !enabled);
                    badge.classList.toggle('text-slate-600', !enabled);
                }

                if (btn) {
                    btn.setAttribute('title', enabled ? 'Deaktivieren' : 'Aktivieren');
                    btn.setAttribute('aria-label', enabled ? 'Modul deaktivieren' : 'Modul aktivieren');
                    btn.classList.toggle('text-emerald-600', enabled);
                    btn.classList.toggle('hover:bg-emerald-50', enabled);
                    btn.classList.toggle('text-slate-400', !enabled);
                    btn.classList.toggle('hover:bg-slate-100', !enabled);
                    btn.classList.toggle('hover:text-slate-600', !enabled);
                }

                if (input) {
                    input.value = enabled ? '0' : '1';
                }
            }

            root.querySelectorAll('[data-module-toggle-form]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const key = form.getAttribute('data-module-toggle-form');
                    const input = root.querySelector('[data-module-enabled-input="' + key + '"]');
                    const nextEnabled = input && input.value === '1';

                    try {
                        const response = await fetch(form.action, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                            },
                            body: new URLSearchParams({
                                enabled: nextEnabled ? '1' : '0'
                            }).toString()
                        });

                        const payload = await response.json();

                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Modulstatus konnte nicht aktualisiert werden.');
                        }

                        paintState(payload.module_key, !!payload.enabled);
                        showToast(payload.message || 'Modulstatus wurde aktualisiert.', 'success');
                    } catch (error) {
                        showToast(error.message || 'Fehler beim Aktualisieren des Modulstatus.', 'error');
                    }
                });
            });
        })();
    </script>
@endsection
