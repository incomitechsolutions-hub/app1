@extends('layouts.public')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('title', 'Alle Kategorien · '.config('app.name'))

@section('content')
    <section class="bg-gradient-to-r from-indigo-700 to-indigo-900 py-12 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">Unsere Schulungskategorien</h1>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100">
                Entdecken Sie unser Weiterbildungsangebot nach Themenfeldern. Wählen Sie eine Kategorie, um Unterkategorien und passende Inhalte zu sehen.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($categories->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Aktuell sind keine Kategorien veröffentlicht.
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    @php
                        $imageUrl = null;
                        if ($category->headerMedia) {
                            $imageUrl = Storage::disk($category->headerMedia->disk)->url($category->headerMedia->file_path);
                        }
                    @endphp
                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <a href="{{ route('public.categories.show', ['slug' => $category->slug]) }}" class="block">
                            <div class="h-44 w-full bg-gray-100 dark:bg-gray-700">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500">Kein Bild</div>
                                @endif
                            </div>
                            <div class="space-y-3 p-5">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category->name }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ Str::limit((string) ($category->description ?? ''), 140) ?: 'Zur Kategorie wechseln und Unterkategorien entdecken.' }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $category->published_children_count }} Unterkategorien</span>
                                    <span class="font-semibold text-primary-600 dark:text-primary-300">Kategorie ansehen →</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection

