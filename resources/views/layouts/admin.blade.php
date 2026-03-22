<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside
            class="hidden w-60 shrink-0 border-r border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:z-30 lg:flex lg:flex-col">
            <div class="flex h-14 items-center border-b border-slate-200 px-4">
                <a href="{{ route('admin.dashboard') }}" class="text-base font-semibold text-slate-900">
                    {{ config('app.name') }}
                </a>
            </div>
            <div class="flex-1 overflow-y-auto px-3 py-4">
                <x-admin.navigation />
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col lg:pl-60">
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

            {{-- Top bar --}}
            <header class="hidden h-14 items-center justify-end gap-4 border-b border-slate-200 bg-white px-6 lg:flex">
                <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                @auth
                    <form method="post" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-slate-700 hover:text-slate-900">
                            Abmelden
                        </button>
                    </form>
                @endauth
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
