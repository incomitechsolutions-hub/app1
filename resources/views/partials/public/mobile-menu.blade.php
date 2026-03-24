@php
    $navigation = config('navigation');
@endphp

<div x-cloak x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50" @click="mobileMenuOpen = false"></div>

<aside
    x-cloak
    x-show="mobileMenuOpen"
    x-transition:enter="transform transition ease-out duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transform transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed inset-y-0 right-0 z-50 w-full max-w-sm bg-white shadow-xl dark:bg-gray-800"
    x-data="{ openMenus: {}, openSections: {} }"
    role="dialog"
    aria-modal="true"
    aria-label="Mobile Navigation">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
            <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $navigation['brand']['name'] }}</span>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-700 transition hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:text-gray-200"
                @click="mobileMenuOpen = false"
                aria-label="Mobile Navigation schließen">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-5 py-4" aria-label="Mobile Hauptnavigation">
            <div class="space-y-4">
                @foreach($navigation['mega_menus'] as $menuIndex => $menu)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            @click="openMenus['menu-{{ $menuIndex }}'] = !openMenus['menu-{{ $menuIndex }}']"
                            :aria-expanded="(openMenus['menu-{{ $menuIndex }}'] ?? false).toString()">
                            <span>{{ $menu['label'] }}</span>
                            <span class="text-gray-500 transition-transform" :class="openMenus['menu-{{ $menuIndex }}'] ? 'rotate-180' : ''">▼</span>
                        </button>

                        <div x-cloak x-show="openMenus['menu-{{ $menuIndex }}']" x-transition class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                            @foreach($menu['sections'] as $sectionIndex => $section)
                                <div class="mb-2 last:mb-0">
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-200"
                                        @click="openSections['section-{{ $menuIndex }}-{{ $sectionIndex }}'] = !openSections['section-{{ $menuIndex }}-{{ $sectionIndex }}']"
                                        :aria-expanded="(openSections['section-{{ $menuIndex }}-{{ $sectionIndex }}'] ?? false).toString()">
                                        <span>{{ $section['title'] }}</span>
                                        <span class="text-gray-400 transition-transform" :class="openSections['section-{{ $menuIndex }}-{{ $sectionIndex }}'] ? 'rotate-180' : ''">▼</span>
                                    </button>

                                    <ul x-cloak x-show="openSections['section-{{ $menuIndex }}-{{ $sectionIndex }}']" class="space-y-2 pb-2 pl-2" x-transition>
                                        @foreach($section['links'] as $link)
                                            <li>
                                                <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-2 text-sm text-gray-600 transition hover:bg-primary-50 hover:text-primary-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-300">
                                                    {{ $link['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach

                            <a href="{{ $menu['view_all']['href'] }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-300">
                                <span>{{ $menu['view_all']['label'] }}</span>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                @endforeach

                <div class="space-y-1 border-t border-gray-200 pt-4 dark:border-gray-700">
                    @foreach($navigation['simple_links'] as $link)
                        <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 transition hover:bg-primary-50 hover:text-primary-700 dark:text-white dark:hover:bg-gray-700 dark:hover:text-primary-300">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>
    </div>
</aside>
