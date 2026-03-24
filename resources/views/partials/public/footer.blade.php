@php
    $navigation = config('navigation');
@endphp

<footer class="bg-primary-900 py-12 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 pb-10 lg:grid-cols-4 lg:gap-8">
            <div class="lg:col-span-1">
                <a href="{{ url('/') }}" class="text-xl font-bold">{{ $navigation['brand']['name'] }}</a>
                <p class="mt-4 text-sm text-primary-100">{{ $navigation['brand']['tagline'] }}</p>

                <a
                    href="{{ $navigation['brand']['cta']['href'] }}"
                    class="mt-6 inline-flex items-center rounded-lg bg-primary-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    {{ $navigation['brand']['cta']['label'] }}
                </a>

                <div class="mt-6 flex items-center gap-2">
                    @foreach($navigation['social_links'] as $social)
                        <a
                            href="{{ $social['href'] }}"
                            target="_blank"
                            rel="noreferrer"
                            aria-label="{{ $social['label'] }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary-800 text-xs font-semibold text-primary-100 transition hover:bg-white/10 hover:text-white">
                            {{ strtoupper(substr($social['label'], 0, 1)) }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2 lg:col-span-3 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                @foreach($navigation['footer_groups'] as $group)
                    <x-public.footer-group :group="$group" />
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-primary-700 pt-6 text-sm text-primary-100 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ $navigation['contact']['phone_href'] }}" class="transition hover:text-white">{{ $navigation['contact']['phone'] }}</a>
                <a href="{{ $navigation['contact']['email_href'] }}" class="transition hover:text-white">{{ $navigation['contact']['email'] }}</a>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                @foreach($navigation['legal_links'] as $link)
                    <a href="{{ $link['href'] }}" class="transition hover:text-white">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
