@php
    $groups = config('admin.navigation', []);
@endphp

<nav class="space-y-7 text-sm" aria-label="{{ __('Admin navigation') }}">
    @foreach ($groups as $group)
        <div class="space-y-2">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                {{ $group['heading'] }}
            </p>
            <ul class="space-y-1">
                @foreach ($group['items'] as $item)
                    @php
                        $pattern = $item['active'] ?? $item['route'];
                        $isActive = request()->routeIs($pattern);
                        $hasChildren = !empty($item['children']);
                        $childActive = false;
                        if ($hasChildren) {
                            foreach ($item['children'] as $childItem) {
                                $childPattern = $childItem['active'] ?? $childItem['route'];
                                if (request()->routeIs($childPattern)) {
                                    $childActive = true;
                                    break;
                                }
                            }
                        }
                        $isExpanded = $isActive || $childActive;
                    @endphp
                    <li @if ($hasChildren) x-data="{ open: {{ $isExpanded ? 'true' : 'false' }} }" @endif>
                        <div class="space-y-1">
                            <div class="flex items-center gap-1">
                                <a href="{{ route($item['route']) }}"
                                    @class([
                                        'group flex min-w-0 flex-1 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-300',
                                        'bg-indigo-50 text-indigo-600 shadow-sm' => $isActive || $childActive,
                                        'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => !($isActive || $childActive),
                                    ])
                                    @if ($isActive || $childActive) aria-current="page" @endif>
                                    <span
                                        @class([
                                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border transition',
                                            'border-indigo-100 bg-white text-indigo-500' => $isActive || $childActive,
                                            'border-slate-200 bg-white text-slate-400 group-hover:text-slate-600' => !($isActive || $childActive),
                                        ])>
                                        @switch($item['icon'] ?? 'dot')
                                            @case('dashboard')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 3.75A.75.75 0 0 1 3.75 3h4.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-4.5A.75.75 0 0 1 3 8.25v-4.5ZM11 3.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-2.5ZM3 11.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.5ZM11 10.75a.75.75 0 0 1 1.5 0v5.5a.75.75 0 0 1-1.5 0v-5.5Zm3 1a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Z" /></svg>
                                                @break
                                            @case('inbox')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.5 4.25A2.25 2.25 0 0 1 4.75 2h10.5A2.25 2.25 0 0 1 17.5 4.25v11.5A2.25 2.25 0 0 1 15.25 18H4.75A2.25 2.25 0 0 1 2.5 15.75V4.25Zm2 .75a.5.5 0 0 0-.5.5v6.19c0 .175.09.338.239.43l2.88 1.777a1 1 0 0 0 1.047 0L11 12.22a1 1 0 0 1 1.047 0l2.88 1.777a.5.5 0 0 0 .763-.43V5.5a.5.5 0 0 0-.5-.5H4.5Z" /></svg>
                                                @break
                                            @case('folder')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 5.25A2.25 2.25 0 0 1 4.25 3h3.568a2.25 2.25 0 0 1 1.591.659l.932.932a.75.75 0 0 0 .53.219h5.879A2.25 2.25 0 0 1 19 7.06v7.69A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm2.25-.75a.75.75 0 0 0-.75.75v9.5c0 .414.336.75.75.75h12.5a.75.75 0 0 0 .75-.75V7.06a.75.75 0 0 0-.75-.75h-5.879a2.25 2.25 0 0 1-1.59-.659l-.932-.932a.75.75 0 0 0-.53-.219H4.25Z" /></svg>
                                                @break
                                            @case('document')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4.75 2A2.75 2.75 0 0 0 2 4.75v10.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25V8.561a2.75 2.75 0 0 0-.805-1.945l-3.81-3.81A2.75 2.75 0 0 0 11.439 2H4.75Zm6 1.75v3.5a1 1 0 0 0 1 1h3.5v7a1.25 1.25 0 0 1-1.25 1.25H4.75A1.25 1.25 0 0 1 3.5 15.25V4.75A1.25 1.25 0 0 1 4.75 3.5h6Z" /></svg>
                                                @break
                                            @case('page')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 3.75A1.75 1.75 0 0 1 5.75 2h8.5A1.75 1.75 0 0 1 16 3.75v12.5A1.75 1.75 0 0 1 14.25 18h-8.5A1.75 1.75 0 0 1 4 16.25V3.75Zm2 .75a.75.75 0 0 0-.75.75v.75a.75.75 0 0 0 .75.75h8a.75.75 0 0 0 .75-.75V5.25a.75.75 0 0 0-.75-.75H6Zm0 4a.75.75 0 0 0 0 1.5h8a.75.75 0 0 0 0-1.5H6Zm0 3.5a.75.75 0 0 0 0 1.5h5a.75.75 0 0 0 0-1.5H6Z" /></svg>
                                                @break
                                            @case('sliders')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 5.75a.75.75 0 0 1 .75-.75H10a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.75ZM3 14.25a.75.75 0 0 1 .75-.75H7a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75ZM13 14.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75ZM12 5.75a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 0 1.5h-3.5a.75.75 0 0 1-.75-.75Zm-2.47-1.28a1.78 1.78 0 1 1-3.56 0 1.78 1.78 0 0 1 3.56 0Zm4.5 10.06a1.78 1.78 0 1 1-3.56 0 1.78 1.78 0 0 1 3.56 0Z" /></svg>
                                                @break
                                            @case('puzzle')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.1 2.5a1.8 1.8 0 0 0-1.8 1.8v.7H6.8A1.8 1.8 0 0 0 5 6.8v1.5H4.3a1.8 1.8 0 1 0 0 3.4H5v1.5a1.8 1.8 0 0 0 1.8 1.8h1.5v.7a1.8 1.8 0 1 0 3.4 0v-.7h1.5a1.8 1.8 0 0 0 1.8-1.8v-1.5h.7a1.8 1.8 0 1 0 0-3.4h-.7V6.8A1.8 1.8 0 0 0 13.2 5h-1.5v-.7a1.8 1.8 0 0 0-1.6-1.8Z" /></svg>
                                                @break
                                            @case('pin')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1.75a6 6 0 0 0-6 6c0 4.254 4.86 9.532 5.067 9.754a1.25 1.25 0 0 0 1.866 0C11.14 17.282 16 12.004 16 7.75a6 6 0 0 0-6-6Zm0 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" /></svg>
                                                @break
                                            @case('question')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8.75-3a.75.75 0 0 0-1.5 0v.25a.75.75 0 0 0 1.5 0V7Zm0 2.5a.75.75 0 0 0-1.5 0V13a.75.75 0 0 0 1.5 0V9.5Zm2.94-1.73a2.69 2.69 0 0 0-4.57 1.23.75.75 0 0 0 1.48.24c.06-.38.28-.7.62-.92.76-.48 1.8.04 1.8.94 0 .44-.25.83-.65 1.02l-.27.14A2.22 2.22 0 0 0 9.5 12.4V13a.75.75 0 0 0 1.5 0v-.6c0-.28.16-.53.41-.66l.27-.14a2.64 2.64 0 0 0 .52-3.83Z" clip-rule="evenodd" /></svg>
                                                @break
                                            @case('image')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 5.75A2.75 2.75 0 0 1 4.75 3h10.5A2.75 2.75 0 0 1 18 5.75v8.5A2.75 2.75 0 0 1 15.25 17H4.75A2.75 2.75 0 0 1 2 14.25v-8.5Zm3.75-.25a1.75 1.75 0 0 0-1.75 1.75v5.416l2.356-2.356a1.25 1.25 0 0 1 1.768 0l1.58 1.58 2.644-2.644a1.25 1.25 0 0 1 1.768 0L16 11.13V7.25a1.75 1.75 0 0 0-1.75-1.75H5.75ZM7.5 8A1.25 1.25 0 1 0 7.5 5.5 1.25 1.25 0 0 0 7.5 8Z" clip-rule="evenodd" /></svg>
                                                @break
                                            @case('arrow-path')
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M15.312 3.53a.75.75 0 0 1 1.06 0l1.75 1.75a.75.75 0 0 1 0 1.06l-1.75 1.75a.75.75 0 1 1-1.06-1.06l.47-.47h-4.03a3.75 3.75 0 0 0-3.63 2.806.75.75 0 0 1-1.453-.382A5.25 5.25 0 0 1 11.75 5.06h4.03l-.47-.47a.75.75 0 0 1 0-1.06Zm-2.98 7.085a.75.75 0 0 1 1.453.383A5.25 5.25 0 0 1 8.25 14.94H4.22l.47.47a.75.75 0 1 1-1.06 1.06l-1.75-1.75a.75.75 0 0 1 0-1.06l1.75-1.75a.75.75 0 0 1 1.06 1.06l-.47.47h4.03a3.75 3.75 0 0 0 3.63-2.805Z" clip-rule="evenodd" /></svg>
                                                @break
                                            @default
                                                <span class="h-2 w-2 rounded-full bg-current"></span>
                                        @endswitch
                                    </span>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>

                                @if ($hasChildren)
                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                        aria-label="Untermenü umschalten">
                                        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            @if ($hasChildren)
                                <ul x-cloak x-show="open" x-transition class="space-y-1 pl-12">
                                    @foreach ($item['children'] as $childItem)
                                        @php
                                            $childPattern = $childItem['active'] ?? $childItem['route'];
                                            $isChildActive = request()->routeIs($childPattern);
                                        @endphp
                                        <li>
                                            <a href="{{ route($childItem['route']) }}"
                                                @class([
                                                    'block rounded-lg px-3 py-2 text-xs font-medium transition',
                                                    'bg-slate-100 text-slate-900' => $isChildActive,
                                                    'text-slate-500 hover:bg-slate-50 hover:text-slate-700' => ! $isChildActive,
                                                ])>
                                                {{ $childItem['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
