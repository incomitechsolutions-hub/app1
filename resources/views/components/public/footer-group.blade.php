@props(['group'])

<div>
    <div class="hidden lg:block">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-white">{{ $group['title'] }}</h3>
        <ul class="mt-4 space-y-3">
            @foreach($group['links'] as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="text-sm text-primary-100 transition hover:text-white">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <details class="group border-b border-primary-700 py-3 lg:hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold text-white [&::-webkit-details-marker]:hidden">
            <span>{{ $group['title'] }}</span>
            <span class="text-primary-200 transition-transform group-open:rotate-180">▼</span>
        </summary>
        <ul class="mt-3 space-y-3 pb-2">
            @foreach($group['links'] as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="text-sm text-primary-100 transition hover:text-white">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </details>
</div>
