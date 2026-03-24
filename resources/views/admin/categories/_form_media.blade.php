@php
    /** @var \App\Domain\Taxonomy\Models\Category|null $category */
    $iconId = old('icon_media_asset_id', $category?->icon_media_asset_id);
    $headerId = old('header_media_asset_id', $category?->header_media_asset_id);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
    <h2 class="mb-2 text-lg font-semibold text-slate-900">Medien</h2>
    <p class="mb-6 text-sm text-slate-500">Icon und Header-Bild sind optional. Sie können eine Datei aus der Bibliothek wählen oder unten eine neue hochladen (überschreibt die Auswahl).</p>
    <div class="grid gap-8 md:grid-cols-2">
        <div class="space-y-3">
            <div>
                <label for="icon_media_asset_id" class="mb-1 block text-sm font-medium text-slate-700">Icon (Bibliothek)</label>
                <select id="icon_media_asset_id" name="icon_media_asset_id"
                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">— Kein Icon —</option>
                    @foreach ($mediaAssets as $asset)
                        <option value="{{ $asset->id }}" @selected((string) $iconId === (string) $asset->id)>
                            #{{ $asset->id }} · {{ $asset->file_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="icon_upload" class="mb-1 block text-sm font-medium text-slate-700">Icon hochladen</label>
                <input id="icon_upload" type="file" name="icon_upload" accept="image/*,.svg"
                    class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
            </div>
            @if (isset($category) && $category->iconMedia)
                <div class="rounded-lg border border-slate-100 bg-slate-50/80 p-3">
                    <p class="text-xs font-medium text-slate-500">Vorschau (gespeichert)</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($category->iconMedia->disk)->url($category->iconMedia->file_path) }}"
                        alt="" class="mt-2 h-20 w-20 rounded-lg border border-slate-200 object-cover">
                </div>
            @endif
            @error('icon_media_asset_id')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror
            @error('icon_upload')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="space-y-3">
            <div>
                <label for="header_media_asset_id" class="mb-1 block text-sm font-medium text-slate-700">Header-Bild (Bibliothek)</label>
                <select id="header_media_asset_id" name="header_media_asset_id"
                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">— Kein Header-Bild —</option>
                    @foreach ($mediaAssets as $asset)
                        <option value="{{ $asset->id }}" @selected((string) $headerId === (string) $asset->id)>
                            #{{ $asset->id }} · {{ $asset->file_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="header_upload" class="mb-1 block text-sm font-medium text-slate-700">Header-Bild hochladen</label>
                <input id="header_upload" type="file" name="header_upload" accept="image/*,.svg"
                    class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
            </div>
            @if (isset($category) && $category->headerMedia)
                <div class="rounded-lg border border-slate-100 bg-slate-50/80 p-3">
                    <p class="text-xs font-medium text-slate-500">Vorschau (gespeichert)</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($category->headerMedia->disk)->url($category->headerMedia->file_path) }}"
                        alt="" class="mt-2 max-h-32 w-full max-w-md rounded-lg border border-slate-200 object-cover">
                </div>
            @endif
            @error('header_media_asset_id')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror
            @error('header_upload')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <p class="mt-6 text-sm text-slate-600">
        <a href="{{ route('admin.media.index') }}" class="font-semibold text-sky-600 hover:text-sky-800">Medienverwaltung öffnen</a>
    </p>
</div>
