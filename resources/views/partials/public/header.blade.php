@php
    $navigation = config('navigation');
@endphp

<header class="sticky top-0 z-50 bg-white shadow-md dark:bg-gray-800">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Hauptnavigation">
        <div class="flex h-20 items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="text-lg font-bold text-gray-900 transition hover:text-primary-600 dark:text-white dark:hover:text-primary-300">
                {{ $navigation['brand']['name'] }}
            </a>

            <div class="hidden items-center gap-8 lg:flex">
                @foreach($navigation['mega_menus'] as $menu)
                    <x-public.mega-menu :menu="$menu" />
                @endforeach

                @foreach($navigation['simple_links'] as $link)
                    <a href="{{ $link['href'] }}" class="text-sm font-semibold text-gray-900 transition-colors hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-white dark:hover:text-primary-300">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <x-public.theme-toggle />

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-700 transition hover:border-primary-300 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:text-white lg:hidden"
                    @click="mobileMenuOpen = true"
                    aria-label="Mobile Navigation öffnen">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm0 5A.75.75 0 0 1 3.5 8.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm0 5a.75.75 0 0 1 .75-.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>
</header>
