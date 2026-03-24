@props(['menu'])

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
        <span>{{ $menu['label'] }}</span>
        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.left
        class="absolute left-0 top-full z-40 mt-1 w-screen max-w-4xl rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
        <div class="grid grid-cols-2 gap-6 p-6">
            @foreach($menu['sections'] as $section)
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $section['title'] }}</h3>
                    <ul class="mt-3 space-y-3">
                        @foreach($section['links'] as $link)
                            <li>
                                <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-2 transition hover:bg-primary-50 hover:text-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:hover:bg-gray-700 dark:hover:text-primary-300">
                                    <span class="block text-sm font-medium">{{ $link['label'] }}</span>
                                    @if(!empty($link['description']))
                                        <span class="block text-xs text-gray-500 dark:text-gray-300">{{ $link['description'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            <a href="{{ $menu['view_all']['href'] }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 transition hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200">
                <span>{{ $menu['view_all']['label'] }}</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</div>
