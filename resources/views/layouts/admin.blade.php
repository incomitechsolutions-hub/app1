<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ mobileSidebarOpen: false }"
    @keydown.escape.window="mobileSidebarOpen = false"
    x-effect="document.body.style.overflow = mobileSidebarOpen ? 'hidden' : ''"
    class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen bg-[#f6f8fb]">
        <div x-cloak x-show="mobileSidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            @click="mobileSidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col border-r border-slate-100 bg-white transition-transform duration-300 lg:z-30"
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="flex h-20 items-center border-b border-slate-100 px-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-lime-400 text-sm font-bold text-white">
                        {{ \Illuminate\Support\Str::substr(config('app.name'), 0, 1) }}
                    </span>
                    <span class="text-lg font-semibold text-slate-900">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-5">
                <x-admin.navigation />
            </div>

            <div class="border-t border-slate-100 px-4 py-4">
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-500 text-sm font-semibold text-white">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="truncate text-sm font-medium text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                    @auth
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg p-1.5 text-slate-500 transition hover:bg-white hover:text-red-600"
                                aria-label="Abmelden">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </aside>

        <div class="min-h-screen lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-100 bg-white/95 backdrop-blur">
                <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden"
                            @click="mobileSidebarOpen = true" aria-label="Menü öffnen">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Admin</p>
                            <p class="text-base font-semibold text-slate-900">@yield('breadcrumb', 'Dashboard')</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="hidden text-right sm:block">
                            <time class="block text-xs font-semibold tabular-nums text-slate-400" datetime="{{ now()->toIso8601String() }}">
                                {{ now()->format('d.m.Y') }}
                            </time>
                            <span class="block text-sm font-semibold tabular-nums text-slate-900">{{ now()->format('H:i') }}</span>
                        </div>

                        <button type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                            aria-label="Benachrichtigungen">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </button>

                        <div class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 sm:flex">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </span>
                            <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif
                @yield('content')
            </main>

            <footer class="px-4 pb-6 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-500">
                    {{ config('app.name') }} Adminbereich
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
