@php
    $navigation = config('navigation');
@endphp
@inject('categoryNavigation', 'App\Domain\Taxonomy\Services\PublicCategoryNavigationService')
@php
    $headerCategories = $categoryNavigation->topCategoriesWithChildren();
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

                <div
                    class="relative"
                    x-data="{ open: false, closeDelay: null }"
                    @mouseenter="clearTimeout(closeDelay); open = true"
                    @mouseleave="closeDelay = setTimeout(() => open = false, 120)">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-gray-900 transition-colors hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-white dark:hover:text-primary-300"
                        @focus="open = true"
                        @click.prevent="open = !open"
                        :aria-expanded="open.toString()">
                        <span>Kategorien</span>
                        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="open"
                        x-transition.origin.top.left
                        class="absolute left-0 top-full z-40 mt-1 w-screen max-w-4xl rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div class="grid grid-cols-3 gap-6 p-6">
                            @foreach($headerCategories as $topCategory)
                                <div>
                                    <a href="{{ route('public.categories.show', ['slug' => $topCategory->slug]) }}"
                                        class="text-sm font-semibold text-gray-900 hover:text-primary-600 dark:text-white dark:hover:text-primary-300">
                                        {{ $topCategory->name }}
                                    </a>
                                    @if($topCategory->children->isNotEmpty())
                                        <ul class="mt-3 space-y-2">
                                            @foreach($topCategory->children as $childCategory)
                                                <li>
                                                    <a href="{{ route('public.categories.show', ['slug' => $childCategory->slug]) }}"
                                                        class="block rounded-lg px-2 py-1 text-sm text-gray-600 transition hover:bg-primary-50 hover:text-primary-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-300">
                                                        {{ $childCategory->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                            <a href="{{ route('public.categories.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 transition hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200">
                                <span>Alle Kategorien</span>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </div>

                @foreach($navigation['simple_links'] as $link)
                    @continue(($link['label'] ?? '') === 'Alle Kategorien')
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
