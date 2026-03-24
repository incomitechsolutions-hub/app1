<?php

namespace App\Domain\Media\Http\Controllers\Admin;

use App\Domain\Media\Http\Requests\Admin\StoreMediaAssetRequest;
use App\Domain\Media\Models\MediaAsset;
use App\Domain\Media\Services\MediaStorageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    public function __construct(
        private readonly MediaStorageService $mediaStorage,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $query = MediaAsset::query()->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where('file_name', 'like', $like)
                    ->orWhere('alt_text', 'like', $like);
            });
        }

        $assets = $query->paginate(24)->withQueryString();

        return view('admin.media.index', [
            'assets' => $assets,
            'search' => $search,
        ]);
    }

    public function store(StoreMediaAssetRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->mediaStorage->store($request->file('file'), $validated['alt_text'] ?? null);

        $query = array_filter($request->only('search'), fn ($v) => $v !== null && $v !== '');

        return redirect()
            ->route('admin.media.index', $query)
            ->with('status', __('Datei wurde hochgeladen.'));
    }
}
