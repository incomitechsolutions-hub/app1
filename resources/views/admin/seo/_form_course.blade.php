@php
    /** @var \App\Domain\Seo\Models\SeoMeta|null $seoMeta */
    /** @var \App\Domain\CourseCatalog\Models\Course|null $course */
    $schemaDefault = $seoMeta?->schema_json
        ? json_encode($seoMeta->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        : null;
    $robotsIndex = old('seo.robots_index', $seoMeta?->robots_index ?? true);
    $robotsFollow = old('seo.robots_follow', $seoMeta?->robots_follow ?? true);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
    <div class="mb-4 rounded-lg border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-900">
        <strong>KI-Assistent:</strong> Modell für KI-Generierung ist nicht konfiguriert. Die KI-Schaltflächen sind Platzhalter.
    </div>

    <h2 class="mb-2 text-lg font-semibold text-slate-900">SEO Meta-Informationen</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <div class="mb-1 flex items-center justify-between gap-2">
                <label for="seo_seo_title" class="text-sm font-medium text-slate-700">SEO Titel</label>
                <button type="button" class="inline-flex items-center gap-1 rounded border border-sky-200 bg-white px-2 py-0.5 text-xs font-medium text-sky-700"
                    onclick="alert('KI-Assistent ist nicht konfiguriert.')">KI</button>
            </div>
            <input id="seo_seo_title" type="text" name="seo[seo_title]" maxlength="255"
                value="{{ old('seo.seo_title', $seoMeta?->seo_title) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            <p class="mt-1 text-xs text-slate-500">{{ mb_strlen((string) old('seo.seo_title', $seoMeta?->seo_title ?? '')) }} / 60 Zeichen (Empfohlen: 50–60)</p>
            @error('seo.seo_title')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <div class="mb-1 flex items-center justify-between gap-2">
                <label for="seo_meta_description" class="text-sm font-medium text-slate-700">SEO Beschreibung</label>
                <button type="button" class="inline-flex items-center gap-1 rounded border border-sky-200 bg-white px-2 py-0.5 text-xs font-medium text-sky-700"
                    onclick="alert('KI-Assistent ist nicht konfiguriert.')">KI</button>
            </div>
            <textarea id="seo_meta_description" name="seo[meta_description]" rows="3" maxlength="1000"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('seo.meta_description', $seoMeta?->meta_description) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">{{ mb_strlen((string) old('seo.meta_description', $seoMeta?->meta_description ?? '')) }} / 160 Zeichen (Empfohlen: 120–160)</p>
            @error('seo.meta_description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <h2 class="mb-4 mt-8 text-lg font-semibold text-slate-900">Keywords &amp; Tags</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <div class="mb-1 flex items-center justify-between gap-2">
                <label for="seo_focus_keyword" class="text-sm font-medium text-slate-700">Fokus-Schlüsselwort</label>
                <button type="button" class="inline-flex items-center gap-1 rounded border border-sky-200 bg-white px-2 py-0.5 text-xs font-medium text-sky-700"
                    onclick="alert('KI-Assistent ist nicht konfiguriert.')">KI</button>
            </div>
            <input id="seo_focus_keyword" type="text" name="seo[focus_keyword]" maxlength="255"
                value="{{ old('seo.focus_keyword', $seoMeta?->focus_keyword) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @error('seo.focus_keyword')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <div class="mb-1 flex items-center justify-between gap-2">
                <label for="seo_tags_csv" class="text-sm font-medium text-slate-700">Tags (kommagetrennt)</label>
                <button type="button" class="inline-flex items-center gap-1 rounded border border-sky-200 bg-white px-2 py-0.5 text-xs font-medium text-sky-700"
                    onclick="alert('KI-Assistent ist nicht konfiguriert.')">KI</button>
            </div>
            <input id="seo_tags_csv" type="text" name="seo[tags_csv]" placeholder="tag1, tag2, tag3" maxlength="2000"
                value="{{ old('seo.tags_csv', $seoMeta?->tags_csv) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            <p class="mt-1 text-xs text-slate-500">Mehrere Tags mit Komma trennen.</p>
            @error('seo.tags_csv')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <h2 class="mb-4 mt-8 text-lg font-semibold text-slate-900">URLs</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <div class="mb-1 flex items-center justify-between gap-2">
                <label for="seo_preview_image_url" class="text-sm font-medium text-slate-700">Vorschaubild URL</label>
                <button type="button" class="inline-flex items-center gap-1 rounded border border-sky-200 bg-white px-2 py-0.5 text-xs font-medium text-sky-700"
                    onclick="alert('KI-Assistent ist nicht konfiguriert.')">KI</button>
            </div>
            <input id="seo_preview_image_url" type="text" name="seo[preview_image_url]" placeholder="https://example.com/image.jpg" maxlength="2048"
                value="{{ old('seo.preview_image_url', $seoMeta?->preview_image_url) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @error('seo.preview_image_url')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <div class="mb-1 flex items-center justify-between gap-2">
                <label for="seo_landing_page_url" class="text-sm font-medium text-slate-700">Landing Page URL</label>
                <button type="button" class="inline-flex items-center gap-1 rounded border border-sky-200 bg-white px-2 py-0.5 text-xs font-medium text-sky-700"
                    onclick="alert('KI-Assistent ist nicht konfiguriert.')">KI</button>
            </div>
            <input id="seo_landing_page_url" type="text" name="seo[landing_page_url]" placeholder="https://example.com/landing" maxlength="2048"
                value="{{ old('seo.landing_page_url', $seoMeta?->landing_page_url) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @error('seo.landing_page_url')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <h2 class="mb-4 mt-8 text-lg font-semibold text-slate-900">Technisches SEO</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="seo_canonical_url" class="mb-1 block text-sm font-medium text-slate-700">Canonical-URL</label>
            <input id="seo_canonical_url" type="url" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $seoMeta?->canonical_url) }}"
                placeholder="Leer = Standard-URL dieser Seite"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @error('seo.canonical_url')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="seo_robots_index" class="mb-1 block text-sm font-medium text-slate-700">Indexierung</label>
            <select id="seo_robots_index" name="seo[robots_index]"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="1" @selected(filter_var($robotsIndex, FILTER_VALIDATE_BOOLEAN))>index</option>
                <option value="0" @selected(! filter_var($robotsIndex, FILTER_VALIDATE_BOOLEAN))>noindex</option>
            </select>
        </div>
        <div>
            <label for="seo_robots_follow" class="mb-1 block text-sm font-medium text-slate-700">Links folgen</label>
            <select id="seo_robots_follow" name="seo[robots_follow]"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="1" @selected(filter_var($robotsFollow, FILTER_VALIDATE_BOOLEAN))>follow</option>
                <option value="0" @selected(! filter_var($robotsFollow, FILTER_VALIDATE_BOOLEAN))>nofollow</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="seo_og_title" class="mb-1 block text-sm font-medium text-slate-700">Open Graph Titel</label>
            <input id="seo_og_title" type="text" name="seo[og_title]" value="{{ old('seo.og_title', $seoMeta?->og_title) }}" maxlength="255"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
        </div>
        <div class="md:col-span-2">
            <label for="seo_og_description" class="mb-1 block text-sm font-medium text-slate-700">Open Graph Beschreibung</label>
            <textarea id="seo_og_description" name="seo[og_description]" rows="2" maxlength="1000"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('seo.og_description', $seoMeta?->og_description) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="seo_og_image" class="mb-1 block text-sm font-medium text-slate-700">OG-Bild (Medien-ID)</label>
            <select id="seo_og_image" name="seo[og_image_media_asset_id]"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="">— Kein Bild —</option>
                @foreach ($mediaAssets as $asset)
                    <option value="{{ $asset->id }}" @selected((string) old('seo.og_image_media_asset_id', $seoMeta?->og_image_media_asset_id) === (string) $asset->id)>
                        #{{ $asset->id }} · {{ $asset->file_name }}
                    </option>
                @endforeach
            </select>
            @error('seo.og_image_media_asset_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <label for="seo_schema_json" class="mb-1 block text-sm font-medium text-slate-700">Schema.org JSON-LD (optional)</label>
            <textarea id="seo_schema_json" name="seo[schema_json]" rows="6" placeholder='{"@context":"https://schema.org",...}'
                class="font-mono w-full rounded-lg border-slate-300 text-xs shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('seo.schema_json', $schemaDefault) }}</textarea>
            @error('seo.schema_json')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <h2 class="mb-4 mt-8 text-lg font-semibold text-slate-900">Bewertungen</h2>
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="average_rating" class="mb-1 block text-sm font-medium text-slate-700">Durchschnittsbewertung</label>
            <input id="average_rating" name="average_rating" type="number" step="0.01" min="0" max="5"
                value="{{ old('average_rating', $course?->average_rating ?? 0) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            <p class="mt-1 text-xs text-slate-500">Wert zwischen 0 und 5</p>
            @error('average_rating')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="ratings_count" class="mb-1 block text-sm font-medium text-slate-700">Anzahl Bewertungen</label>
            <input id="ratings_count" name="ratings_count" type="number" min="0"
                value="{{ old('ratings_count', $course?->ratings_count ?? 0) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
            @error('ratings_count')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
