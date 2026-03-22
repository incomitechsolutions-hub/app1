@php
    $groups = config('admin.navigation', []);
@endphp

<nav class="space-y-6 text-sm" aria-label="{{ __('Admin navigation') }}">
    @foreach ($groups as $group)
        <div>
            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ $group['heading'] }}
            </p>
            <ul class="space-y-0.5">
                @foreach ($group['items'] as $item)
                    @php
                        $pattern = $item['active'] ?? $item['route'];
                        $isActive = request()->routeIs($pattern);
                    @endphp
                    <li>
                        <a href="{{ route($item['route']) }}"
                            @class([
                                'block rounded-md px-3 py-2 font-medium',
                                'bg-slate-800 text-white' => $isActive,
                                'text-slate-700 hover:bg-slate-100' => ! $isActive,
                            ])
                            @if ($isActive) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
