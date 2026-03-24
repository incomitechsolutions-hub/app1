@extends('layouts.public')

@section('title', 'Course ITS')

@section('content')
    <section class="bg-gradient-to-r from-primary-700 to-primary-900 py-20 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold">Weiterbildung, die Wirkung zeigt</h1>
            <p class="mx-auto mt-6 max-w-3xl text-xl opacity-90">
                Entdecken Sie praxisnahe Trainings fuer IT, Management und Compliance im bewaehrten Sbase/Course ITS Design.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="/kurse" class="rounded-lg bg-white px-6 py-3 font-semibold text-primary-600 transition hover:bg-gray-100">Kurse entdecken</a>
                <a href="/kontakt" class="rounded-lg border-2 border-white px-6 py-3 font-semibold text-white transition hover:bg-white hover:text-primary-600">Beratung anfragen</a>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Public Layout Vorschau</h2>
            <p class="mt-4 text-base text-gray-600 dark:text-gray-300">
                Diese Seite demonstriert den wiederverwendbaren Header, das Mega-Menue, die mobile Off-Canvas Navigation, den Theme Toggle und den strukturierten Footer mit Akkordeon-Verhalten auf kleinen Displays.
            </p>
        </div>
    </section>
@endsection
