<?php

namespace App\Domain\Media\Services;

use App\Domain\Media\Models\MediaAsset;
use Illuminate\Http\UploadedFile;

class MediaStorageService
{
    public function store(UploadedFile $file, ?string $altText = null): MediaAsset
    {
        $path = $file->store('media', 'public');

        return MediaAsset::query()->create([
            'disk' => 'public',
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'alt_text' => $altText,
        ]);
    }
}
