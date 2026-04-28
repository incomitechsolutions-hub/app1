@extends('layouts.public')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $seo = $category->seoMeta;
    $pageTitle = $seo?->seo_title ?: $category->name;
    $metaDescription = $seo?->meta_description ?: Str::limit(strip_tags((string) ($category->description ?? '')), 160);
    $canonical = $seo?->canonical_url ?: route('public.categories.show', ['slug' => $category->slug], true);
    $robotsIndex = $seo?->robots_index ?? true;
    $robotsFollow = $seo?->robots_follow ?? true;
    $ogTitle = $seo?->og_title ?: $category->name;
    $ogDescription = $seo?->og_description ?: $metaDescription;
    $ogImageUrl = null;
    if ($seo?->ogImageMedia) {
        $ogImageUrl = Storage::disk($seo->ogImageMedia->disk)->url($seo->ogImageMedia->file_path);
        if (! str_starts_with((string) $ogImageUrl, 'http')) {
            $ogImageUrl = url($ogImageUrl);
        }
    } elseif ($category->headerMedia) {
        $ogImageUrl = url(Storage::disk($category->headerMedia->disk)->url($category->headerMedia->file_path));
    }
@endphp

@section('title', $pageTitle.' · '.config('app.name'))

@push('meta')
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }},{{ $robotsFollow ? 'follow' : 'nofollow' }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    @if ($ogImageUrl)
        <meta property="og:image" content="{{ $ogImageUrl }}">
    @endif
    <meta property="og:url" content="{{ $canonical }}">
    @if ($seo?->schema_json)
        <script type="application/ld+json">{!! json_encode($seo->schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
@endpush

@section('content')
    <article class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <header class="border-b border-gray-200 pb-8 dark:border-gray-700">
            @if ($category->parent)
                <a href="{{ route('public.categories.show', ['slug' => $category->parent->slug]) }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200">
                    ← Zurück zu {{ $category->parent->name }}
                </a>
            @else
                <a href="{{ route('public.categories.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200">
                    ← Zurück zur Kategorienübersicht
                </a>
            @endif
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">{{ $category->description }}</p>
            @endif
        </header>

        @if ($category->children->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Unterkategorien</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($category->children as $child)
                        <a href="{{ route('public.categories.show', ['slug' => $child->slug]) }}"
                            class="block rounded-xl border border-gray-200 bg-white p-4 transition hover:border-primary-300 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $child->name }}</h3>
                            @if ($child->description)
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($child->description, 120) }}</p>
                            @endif
                            <span class="mt-3 inline-block text-sm font-medium text-primary-600 dark:text-primary-300">Zur Unterkategorie →</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
