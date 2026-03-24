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
    <article class="mx-auto max-w-4xl px-4 py-12">
        <header class="border-b border-gray-200 pb-8 dark:border-gray-700">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">{{ $category->description }}</p>
            @endif
        </header>

        @if ($category->children->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Unterkategorien</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($category->children as $child)
                        <li>
                            <a href="{{ route('public.categories.show', ['slug' => $child->slug]) }}"
                                class="text-sky-600 hover:text-sky-800 dark:text-sky-400">
                                {{ $child->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
@endsection
