<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseCrawlController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $request->validate([
            'source_url' => ['required', 'string', 'url', 'max:2048'],
        ]);

        return redirect()
            ->route('admin.course-catalog.courses.create')
            ->with('crawl_info', __('Crawling ist noch nicht implementiert. URL wurde nicht abgerufen.'));
    }
}
