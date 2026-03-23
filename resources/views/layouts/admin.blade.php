<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside
            class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:z-30 lg:flex lg:flex-col">
            <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-4">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-sky-600 text-sm font-bold text-white shadow-sm"
                    aria-hidden="true">
                    {{ \Illuminate\Support\Str::substr(config('app.name'), 0, 1) }}
                </span>
                <div class="min-w-0">
                    <a href="{{ route('admin.dashboard') }}" class="block truncate text-base font-semibold text-slate-900">
                        {{ config('app.name') }}
                    </a>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-3 py-4">
                <x-admin.navigation />
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
            {{-- Mobile menu --}}
            <header class="border-b border-slate-200 bg-white lg:hidden">
                <details class="group">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-medium text-slate-800 [&::-webkit-details-marker]:hidden">
                        <span>Menü</span>
                        <span class="text-slate-500 group-open:rotate-180 motion-safe:transition">▼</span>
                    </summary>
                    <div class="border-t border-slate-100 px-3 py-4">
                        <x-admin.navigation />
                    </div>
                </details>
            </header>

            {{-- Top bar (desktop) --}}
            <header class="hidden h-14 items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 lg:flex">
                <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    @yield('breadcrumb', 'Dashboard')
                </div>
                <div class="flex items-center gap-5">
                    <time class="text-sm tabular-nums text-slate-600" datetime="{{ now()->toIso8601String() }}">
                        {{ now()->format('d.m.Y') }} · {{ now()->format('H:i') }}
                    </time>
                    <span class="text-slate-300" aria-hidden="true">|</span>
                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        title="Benachrichtigungen" disabled aria-disabled="true">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>
                    <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                    @auth
                        <form method="post" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-slate-700 hover:text-slate-900">
                                Abmelden
                            </button>
                        </form>
                    @endauth
                </div>
            </header>

            <header class="flex h-12 items-center justify-between border-b border-slate-200 bg-white px-4 lg:hidden">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-slate-900">Admin</a>
                @auth
                    <form method="post" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-600">Abmelden</button>
                    </form>
                @endauth
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
