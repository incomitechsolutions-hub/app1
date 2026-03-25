@extends('layouts.public')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $seo = $course->seoMeta;
    $pageTitle = $seo?->seo_title ?: $course->title;
    $metaDescription = $seo?->meta_description ?: Str::limit(strip_tags((string) ($course->short_description ?? '')), 160);
    $canonical = $seo?->canonical_url ?: route('public.courses.show', ['slug' => $course->slug], true);
    $robotsIndex = $seo?->robots_index ?? true;
    $robotsFollow = $seo?->robots_follow ?? true;
    $ogTitle = $seo?->og_title ?: $course->title;
    $ogDescription = $seo?->og_description ?: $metaDescription;
    $ogImageUrl = null;
    if ($seo?->ogImageMedia) {
        $ogImageUrl = Storage::disk($seo->ogImageMedia->disk)->url($seo->ogImageMedia->file_path);
        if (! str_starts_with((string) $ogImageUrl, 'http')) {
            $ogImageUrl = url($ogImageUrl);
        }
    } elseif ($course->heroMedia) {
        $ogImageUrl = url(Storage::disk($course->heroMedia->disk)->url($course->heroMedia->file_path));
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
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $course->title }}</h1>
            @if ($course->short_description)
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">{{ $course->short_description }}</p>
            @endif
            <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                @if ($course->duration_days)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Dauer</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $course->duration_days === 1 ? '1 Tag' : $course->duration_days.' Tage' }}</dd>
                    </div>
                @endif
                @if ($course->price !== null)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Preis</dt>
                        <dd class="text-gray-900 dark:text-white">{{ number_format((float) $course->price, 2, ',', '.') }} {{ $course->currency_code ?? 'EUR' }}</dd>
                    </div>
                @endif
                @if ($course->delivery_format)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Format</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $course->delivery_format->value }}</dd>
                    </div>
                @endif
                @if ($course->primaryCategory)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Primärkategorie</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $course->primaryCategory->name }}</dd>
                    </div>
                @endif
            </dl>
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($course->booking_url)
                    <a href="{{ $course->booking_url }}"
                        class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700"
                        rel="noopener noreferrer" target="_blank">
                        Buchung
                    </a>
                @endif
                @if ($course->offer_url)
                    <a href="{{ $course->offer_url }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                        rel="noopener noreferrer" target="_blank">
                        Angebot
                    </a>
                @endif
            </div>
        </header>

        @if ($course->long_description)
            <div class="prose prose-gray dark:prose-invert mt-10 max-w-none">
                {!! nl2br(e($course->long_description)) !!}
            </div>
        @endif
    </article>
@endsection
