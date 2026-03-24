<div class="admin-panel space-y-4 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Medien-Bereiche</h2>
    <p class="text-sm text-slate-500">Aktivieren Sie optionale Medien-Slots für diesen Kurs.</p>
    @php
        $flags = [
            'media_icon_enabled' => ['label' => 'Icon', 'icon' => 'photo'],
            'media_header_enabled' => ['label' => 'Header-Hintergrund', 'icon' => 'layers'],
            'media_video_enabled' => ['label' => 'Video', 'icon' => 'video'],
            'media_gallery_enabled' => ['label' => 'Galerie', 'icon' => 'gallery'],
        ];
    @endphp
    <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
        @foreach ($flags as $name => $meta)
            <div class="flex items-center justify-between gap-4 px-4 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                        @if ($meta['icon'] === 'photo')
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                        @elseif ($meta['icon'] === 'layers')
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 12m4.179-2.25L12 5.25l5.571 3m0 0 5.25-2.25M17.75 9.75 21.75 12l-4.179 2.25M17.75 9.75l-5.571-3m5.571 3 5.25 2.25m-5.571-3-5.571 3" /></svg>
                        @elseif ($meta['icon'] === 'video')
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        @else
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-9.88 5.25h8.25a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-8.25A2.25 2.25 0 0 0 4.5 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        @endif
                    </span>
                    <span class="text-sm font-medium text-slate-800">{{ $meta['label'] }}</span>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only"
                        @checked(old($name, $course?->{$name} ?? false))>
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all peer-checked:bg-sky-600 peer-checked:after:translate-x-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-sky-300"></div>
                </label>
            </div>
        @endforeach
    </div>
    @foreach (['media_icon_enabled', 'media_header_enabled', 'media_video_enabled', 'media_gallery_enabled'] as $f)
        @error($f)
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endforeach
</div>
