@extends('layouts.public')

@php
    use Illuminate\Support\Str;

    $pageTitle = $program->title;
    $metaDescription = Str::limit(strip_tags((string) ($program->short_description ?? '')), 160);
@endphp

@section('title', $pageTitle.' · '.config('app.name'))

@push('meta')
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ route('public.programs.show', ['slug' => $program->slug], true) }}">
@endpush

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="border-b border-slate-200 pb-8">
            <p class="text-sm font-medium uppercase tracking-wide text-sky-700">Programm</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ $program->title }}</h1>
            @if ($program->short_description)
                <div class="prose prose-slate mt-4 max-w-none text-slate-700">
                    {!! nl2br(e($program->short_description)) !!}
                </div>
            @endif
        </header>

        @if ($program->courses->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-lg font-semibold text-slate-900">Kurse in diesem Programm</h2>
                <ol class="mt-4 list-decimal space-y-3 pl-5 text-slate-800">
                    @foreach ($program->courses as $course)
                        <li>
                            <a href="{{ route('public.courses.show', ['slug' => $course->slug]) }}"
                                class="font-medium text-sky-700 underline decoration-sky-300 underline-offset-2 hover:text-sky-900">
                                {{ $course->title }}
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
    </article>
@endsection
