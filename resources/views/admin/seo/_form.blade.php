@php
    /** @var \App\Domain\Seo\Models\SeoMeta|null $seoMeta */
    $schemaDefault = $seoMeta?->schema_json
        ? json_encode($seoMeta->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        : null;
    $robotsIndex = old('seo.robots_index', $seoMeta?->robots_index ?? true);
    $robotsFollow = old('seo.robots_follow', $seoMeta?->robots_follow ?? true);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
    <h2 class="mb-2 text-lg font-semibold text-slate-900">SEO</h2>
    <p class="mb-6 text-sm text-slate-500">Meta-Angaben für Suchmaschinen und Social. Leere Felder nutzen sinnvolle Fallbacks aus dem Inhalt.</p>
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="seo_seo_title" class="mb-1 block text-sm font-medium text-slate-700">SEO-Titel</label>
            <input id="seo_seo_title" type="text" name="seo[seo_title]" value="{{ old('seo.seo_title', $seoMeta?->seo_title) }}"
                maxlength="255"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('seo.seo_title')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <label for="seo_meta_description" class="mb-1 block text-sm font-medium text-slate-700">Meta-Description</label>
            <textarea id="seo_meta_description" name="seo[meta_description]" rows="3" maxlength="1000"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('seo.meta_description', $seoMeta?->meta_description) }}</textarea>
            @error('seo.meta_description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <label for="seo_canonical_url" class="mb-1 block text-sm font-medium text-slate-700">Canonical-URL</label>
            <input id="seo_canonical_url" type="url" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $seoMeta?->canonical_url) }}"
                placeholder="Leer = Standard-URL dieser Seite"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('seo.canonical_url')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="seo_robots_index" class="mb-1 block text-sm font-medium text-slate-700">Indexierung</label>
            <select id="seo_robots_index" name="seo[robots_index]"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="1" @selected(filter_var($robotsIndex, FILTER_VALIDATE_BOOLEAN))>index</option>
                <option value="0" @selected(! filter_var($robotsIndex, FILTER_VALIDATE_BOOLEAN))>noindex</option>
            </select>
        </div>
        <div>
            <label for="seo_robots_follow" class="mb-1 block text-sm font-medium text-slate-700">Links folgen</label>
            <select id="seo_robots_follow" name="seo[robots_follow]"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="1" @selected(filter_var($robotsFollow, FILTER_VALIDATE_BOOLEAN))>follow</option>
                <option value="0" @selected(! filter_var($robotsFollow, FILTER_VALIDATE_BOOLEAN))>nofollow</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="seo_og_title" class="mb-1 block text-sm font-medium text-slate-700">Open Graph Titel</label>
            <input id="seo_og_title" type="text" name="seo[og_title]" value="{{ old('seo.og_title', $seoMeta?->og_title) }}" maxlength="255"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>
        <div class="md:col-span-2">
            <label for="seo_og_description" class="mb-1 block text-sm font-medium text-slate-700">Open Graph Beschreibung</label>
            <textarea id="seo_og_description" name="seo[og_description]" rows="2" maxlength="1000"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('seo.og_description', $seoMeta?->og_description) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="seo_og_image" class="mb-1 block text-sm font-medium text-slate-700">OG-Bild (Medien-ID)</label>
            <select id="seo_og_image" name="seo[og_image_media_asset_id]"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
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
                class="font-mono w-full rounded-lg border-slate-300 text-xs shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('seo.schema_json', $schemaDefault) }}</textarea>
            @error('seo.schema_json')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
