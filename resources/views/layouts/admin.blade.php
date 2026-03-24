<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{
        sidebarToggle: false,
        sidebarExpanded: localStorage.getItem('adminSidebarExpanded') === 'false' ? false : true,
    }"
    @keydown.escape.window="sidebarToggle = false"
    x-effect="document.body.style.overflow = sidebarToggle ? 'hidden' : ''"
    class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="flex h-screen overflow-hidden bg-slate-50">
        <div x-cloak x-show="sidebarToggle" x-transition.opacity class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
            @click="sidebarToggle = false"></div>

        <aside
            class="no-scrollbar fixed left-0 top-0 z-40 flex h-screen flex-col justify-between overflow-y-auto border-r border-slate-200 bg-white px-4 pt-5 transition-all duration-300"
            :class="[
                sidebarToggle ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarExpanded ? 'w-[250px]' : 'w-[250px] lg:w-20'
            ]">
            <div class="flex items-center pb-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-lime-400 text-sm font-bold text-white">
                        {{ \Illuminate\Support\Str::substr(config('app.name'), 0, 1) }}
                    </span>
                    <span class="text-xl font-bold text-slate-900" x-show="sidebarExpanded || sidebarToggle">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="flex-1">
                <x-admin.navigation />
            </div>

            <div class="border-t border-slate-100 py-3">
                <div class="flex items-center justify-between gap-2 rounded-xl bg-slate-50 px-2 py-2">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-500 text-sm font-semibold text-white">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="truncate text-sm font-medium text-slate-700" x-show="sidebarExpanded || sidebarToggle">{{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                    <div class="flex items-center gap-1" x-show="sidebarExpanded || sidebarToggle">
                        <a href="{{ route('admin.identity.users.index') }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-indigo-600">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 6h9.75M10.5 12h9.75m-9.75 6h9.75M3.75 6h.008v.008H3.75V6Zm0 6h.008v.008H3.75V12Zm0 6h.008v.008H3.75V18Z" />
                            </svg>
                        </a>
                        @auth
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50"
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
            </div>
        </aside>

        <div
            class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden transition-all duration-300"
            :class="sidebarExpanded ? 'lg:ml-[250px]' : 'lg:ml-20'">
            <header class="z-20 flex h-[58px] items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden"
                        @click="sidebarToggle = !sidebarToggle" aria-label="Menü öffnen">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <button type="button"
                        class="hidden h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:inline-flex"
                        @click="sidebarExpanded = !sidebarExpanded; localStorage.setItem('adminSidebarExpanded', sidebarExpanded)">
                        <svg x-show="sidebarExpanded" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256">
                            <path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM40,56H80V200H40ZM216,200H96V56H216V200Z"></path>
                        </svg>
                        <svg x-show="!sidebarExpanded" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256">
                            <path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm0,16V96H40V56ZM40,112H96v88H40Zm176,88H112V112H216v88Z"></path>
                        </svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <div class="h-5 w-px bg-slate-200"></div>
                        <p class="text-lg font-semibold text-slate-900">@yield('breadcrumb', 'Dashboard')</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <time class="block text-xs text-slate-500" datetime="{{ now()->toIso8601String() }}">
                            {{ now()->format('d.m.Y') }}
                        </time>
                        <span class="block text-sm font-bold tabular-nums text-slate-800">{{ now()->format('H : i') }}</span>
                    </div>

                    <button type="button"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                        aria-label="Benachrichtigungen">
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-amber-400"></span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-2.5 py-1.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-700">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[1600px] p-2 md:p-6">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif
                @yield('content')
            </main>

            <footer class="px-2 pb-3 md:px-6">
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs text-slate-500">
                    {{ config('app.name') }} · Admin
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
