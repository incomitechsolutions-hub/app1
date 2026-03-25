@php
    use App\Domain\CourseCatalog\Enums\CourseStatus;
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;

    $d = $draft ?? [];
    $seo = is_array($d['seo'] ?? null) ? $d['seo'] : [];
    $tagIds = old('tag_ids', $d['tag_ids'] ?? []);
    $audienceIds = old('audience_ids', $d['audience_ids'] ?? []);
@endphp

@extends('layouts.admin')

@section('title', 'KI: Entwurf prüfen')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ tab: 'basics' }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">KI-Kurs: Entwurf prüfen</h1>
                <p class="mt-1 text-sm text-slate-500">Sitzung #{{ $session->id }} · Status: {{ $session->status->value }}
                    @if ($session->last_regenerated_section)
                        · Zuletzt regeneriert: {{ $session->last_regenerated_section }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.course-catalog.courses.ai-generation.show', $session) }}"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">Technische Ansicht</a>
                <a href="{{ route('admin.course-catalog.courses.index') }}"
                    class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Zur Kursübersicht</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif
        @if (session('ai_error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">{{ session('ai_error') }}</div>
        @endif

        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-1">
            <button type="button" @click="tab = 'basics'" :class="tab === 'basics' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500'"
                class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold">Basiseinstellungen</button>
            <button type="button" @click="tab = 'details'" :class="tab === 'details' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500'"
                class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold">Details</button>
            <button type="button" @click="tab = 'pricing'" :class="tab === 'pricing' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500'"
                class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold">Preis</button>
            <button type="button" @click="tab = 'seo'" :class="tab === 'seo' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500'"
                class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold">SEO</button>
            <button type="button" @click="tab = 'regen'" :class="tab === 'regen' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500'"
                class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold">Regenerieren</button>
            <button type="button" @click="tab = 'media'" :class="tab === 'media' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500'"
                class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold">Media</button>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.courses.ai-generation.draft.update', $session) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div x-show="tab === 'basics'" class="space-y-6">
                <div class="admin-panel space-y-4 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Basiseinstellungen (Entwurf)</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Titel</label>
                            <input type="text" name="draft[title]" value="{{ old('draft.title', $d['title'] ?? '') }}" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Slug</label>
                            <input type="text" name="draft[slug]" value="{{ old('draft.slug', $d['slug'] ?? '') }}" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Sprache</label>
                            <input type="text" name="draft[language_code]" value="{{ old('draft.language_code', $d['language_code'] ?? 'de') }}" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hauptkategorie</label>
                            <select name="draft[primary_category_id]"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">—</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected((string) old('draft.primary_category_id', $d['primary_category_id'] ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Schwierigkeit</label>
                            <select name="draft[difficulty_level_id]"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">—</option>
                                @foreach ($difficultyLevels as $lvl)
                                    <option value="{{ $lvl->id }}" @selected((string) old('draft.difficulty_level_id', $d['difficulty_level_id'] ?? '') === (string) $lvl->id)>{{ $lvl->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Tags (IDs)</label>
                            <select name="draft[tag_ids][]" multiple size="5"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}" @selected(in_array($tag->id, (array) $tagIds, true))>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Zielgruppen (IDs)</label>
                            <select name="draft[audience_ids][]" multiple size="5"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach ($audiences as $aud)
                                    <option value="{{ $aud->id }}" @selected(in_array($aud->id, (array) $audienceIds, true))>{{ $aud->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'details'" x-cloak class="space-y-6">
                <div class="admin-panel space-y-4 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
                        <textarea name="draft[short_description]" rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('draft.short_description', $d['short_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Langtext</label>
                        <textarea name="draft[long_description]" rows="8"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('draft.long_description', $d['long_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Zielgruppe (Text)</label>
                        <textarea name="draft[target_audience_text]" rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('draft.target_audience_text', $d['target_audience_text'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Voraussetzungen (Text)</label>
                        <textarea name="draft[prerequisites_text]" rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('draft.prerequisites_text', $d['prerequisites_text'] ?? '') }}</textarea>
                    </div>
                    <p class="text-xs text-slate-500">Module, Lernziele, FAQs: strukturierte Daten bleiben aus dem KI-JSON erhalten, wenn Sie nur dieses Formular speichern. Für tiefe Bearbeitung nutzen Sie „Regenerieren“ oder die spätere Kursbearbeitung.</p>
                </div>
            </div>

            <div x-show="tab === 'pricing'" x-cloak class="space-y-6">
                <div class="admin-panel space-y-4 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Preis</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Preis</label>
                            <input type="text" name="draft[price]" value="{{ old('draft.price', $d['price'] ?? '') }}"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Währung</label>
                            <input type="text" name="draft[currency_code]" value="{{ old('draft.currency_code', $d['currency_code'] ?? 'EUR') }}"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Dauer (Tage)</label>
                            <input type="number" name="draft[duration_days]" value="{{ old('draft.duration_days', $d['duration_days'] ?? '') }}"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Format</label>
                            <select name="draft[delivery_format]" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">—</option>
                                @foreach (DeliveryFormat::cases() as $df)
                                    <option value="{{ $df->value }}" @selected(old('draft.delivery_format', $d['delivery_format'] ?? '') === $df->value)>{{ $df->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'seo'" x-cloak class="space-y-6">
                <div class="admin-panel space-y-4 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">SEO</h2>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">SEO-Titel</label>
                        <input type="text" name="draft[seo][seo_title]" value="{{ old('draft.seo.seo_title', $seo['seo_title'] ?? '') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Meta-Beschreibung</label>
                        <textarea name="draft[seo][meta_description]" rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('draft.seo.meta_description', $seo['meta_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Focus Keyword</label>
                        <input type="text" name="draft[seo][focus_keyword]" value="{{ old('draft.seo.focus_keyword', $seo['focus_keyword'] ?? '') }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div x-show="tab === 'regen'" x-cloak class="space-y-6">
                <div class="admin-panel space-y-4 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Abschnitt regenerieren</h2>
                    <p class="text-sm text-slate-600">Wählen Sie einen Abschnitt und optional einen Hinweis. Es wird ein weiterer KI-Aufruf ausgeführt.</p>
                </div>
            </div>

            <div x-show="tab === 'media'" x-cloak class="space-y-6">
                <div class="admin-panel space-y-4 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Media</h2>
                    <p class="text-sm text-slate-600">Medien (Hero-Bild, Galerie) können Sie nach dem Anlegen des Kurses im normalen Kurs-Editor pflegen.</p>
                </div>
            </div>

            <div class="admin-panel p-4">
                <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Entwurf speichern
                </button>
            </div>
        </form>

        <form method="post" action="{{ route('admin.course-catalog.courses.ai-generation.regenerate', $session) }}" class="admin-panel space-y-4 p-6">
            @csrf
            <h3 class="text-sm font-semibold text-slate-800">Regenerieren</h3>
            <div>
                <label class="block text-sm font-medium text-slate-700">Abschnitt</label>
                <select name="section" class="mt-1 block w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="basics">Basics</option>
                    <option value="details_copy">Texte / Details</option>
                    <option value="pricing">Preis</option>
                    <option value="seo">SEO</option>
                    <option value="modules">Module</option>
                    <option value="objectives">Lernziele</option>
                    <option value="prerequisites">Voraussetzungen</option>
                    <option value="faqs">FAQs</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Hinweis (optional)</label>
                <textarea name="hint" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    placeholder="Was soll sich ändern?"></textarea>
            </div>
            <button type="submit" class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-900 hover:bg-sky-100">
                Abschnitt neu generieren
            </button>
        </form>

        <form method="post" action="{{ route('admin.course-catalog.courses.ai-generation.finalize', $session) }}" class="space-y-6">
            @csrf
            <div class="admin-panel space-y-4 p-6">
                <h2 class="text-lg font-semibold text-slate-900">Kurs final anlegen</h2>
                <p class="text-sm text-slate-600">Pflichtfelder wie beim manuellen Anlegen. Werte aus dem Entwurf sind vorausgefüllt.</p>

                <input type="hidden" name="subtitle" value="{{ $d['subtitle'] ?? '' }}">
                <input type="hidden" name="external_course_code" value="">
                <input type="hidden" name="hero_media_asset_id" value="">
                <input type="hidden" name="author_name" value="">
                <input type="hidden" name="content_version" value="">
                <input type="hidden" name="ai_prompt_source" value="ai_course_generation">
                <input type="hidden" name="internal_notes" value="">
                <input type="hidden" name="average_rating" value="">
                <input type="hidden" name="ratings_count" value="">
                <input type="hidden" name="media_icon_enabled" value="0">
                <input type="hidden" name="media_header_enabled" value="0">
                <input type="hidden" name="media_video_enabled" value="0">
                <input type="hidden" name="media_gallery_enabled" value="0">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Titel</label>
                        <input type="text" name="title" value="{{ old('title', $d['title'] ?? '') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $d['slug'] ?? '') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Status</label>
                        <select name="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (CourseStatus::cases() as $st)
                                <option value="{{ $st->value }}" @selected(old('status', $d['status'] ?? CourseStatus::Draft->value) === $st->value)>{{ $st->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Sprache</label>
                        <input type="text" name="language_code" value="{{ old('language_code', $d['language_code'] ?? 'de') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Währung</label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', $d['currency_code'] ?? 'EUR') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
                        <textarea name="short_description" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('short_description', $d['short_description'] ?? '') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Langtext</label>
                        <textarea name="long_description" rows="6" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('long_description', $d['long_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Hauptkategorie</label>
                        <select name="primary_category_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">—</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) old('primary_category_id', $d['primary_category_id'] ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Preis</label>
                        <input type="text" name="price" value="{{ old('price', $d['price'] ?? '') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                @php
                    $mods = old('modules', $d['modules'] ?? []);
                    $objs = old('objectives', $d['objectives'] ?? []);
                    $prqs = old('prerequisites', $d['prerequisites'] ?? []);
                    $faqs = old('faqs', $d['faqs'] ?? []);
                @endphp

                @foreach ($mods as $i => $row)
                    <input type="hidden" name="modules[{{ $i }}][title]" value="{{ is_array($row) ? ($row['title'] ?? '') : '' }}">
                    <input type="hidden" name="modules[{{ $i }}][description]" value="{{ is_array($row) ? ($row['description'] ?? '') : '' }}">
                    <input type="hidden" name="modules[{{ $i }}][duration_hours]" value="{{ is_array($row) ? ($row['duration_hours'] ?? '') : '' }}">
                    <input type="hidden" name="modules[{{ $i }}][sort_order]" value="{{ is_array($row) ? ($row['sort_order'] ?? $i) : $i }}">
                @endforeach
                @foreach ($objs as $i => $row)
                    <input type="hidden" name="objectives[{{ $i }}][objective_text]" value="{{ is_array($row) ? ($row['objective_text'] ?? '') : $row }}">
                    <input type="hidden" name="objectives[{{ $i }}][sort_order]" value="{{ is_array($row) ? ($row['sort_order'] ?? $i) : $i }}">
                @endforeach
                @foreach ($prqs as $i => $row)
                    <input type="hidden" name="prerequisites[{{ $i }}][prerequisite_text]" value="{{ is_array($row) ? ($row['prerequisite_text'] ?? '') : $row }}">
                    <input type="hidden" name="prerequisites[{{ $i }}][sort_order]" value="{{ is_array($row) ? ($row['sort_order'] ?? $i) : $i }}">
                @endforeach
                @foreach ($faqs as $i => $row)
                    @if (is_array($row))
                        <input type="hidden" name="faqs[{{ $i }}][question]" value="{{ $row['question'] ?? '' }}">
                        <input type="hidden" name="faqs[{{ $i }}][answer]" value="{{ $row['answer'] ?? '' }}">
                        <input type="hidden" name="faqs[{{ $i }}][sort_order]" value="{{ $row['sort_order'] ?? $i }}">
                    @endif
                @endforeach

                <input type="hidden" name="seo[seo_title]" value="{{ $seo['seo_title'] ?? '' }}">
                <input type="hidden" name="seo[meta_description]" value="{{ $seo['meta_description'] ?? '' }}">
                <input type="hidden" name="seo[focus_keyword]" value="{{ $seo['focus_keyword'] ?? '' }}">
                <input type="hidden" name="seo[canonical_url]" value="{{ $seo['canonical_url'] ?? '' }}">
                <input type="hidden" name="seo[robots_index]" value="{{ $seo['robots_index'] ?? '' }}">
                <input type="hidden" name="seo[robots_follow]" value="{{ $seo['robots_follow'] ?? '' }}">
                <input type="hidden" name="seo[og_title]" value="{{ $seo['og_title'] ?? '' }}">
                <input type="hidden" name="seo[og_description]" value="{{ $seo['og_description'] ?? '' }}">

                @foreach ($tagIds as $tid)
                    <input type="hidden" name="tag_ids[]" value="{{ $tid }}">
                @endforeach
                @foreach ($audienceIds as $aid)
                    <input type="hidden" name="audience_ids[]" value="{{ $aid }}">
                @endforeach

                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Kurs jetzt anlegen (final)
                </button>
            </div>
        </form>
    </div>
@endsection
