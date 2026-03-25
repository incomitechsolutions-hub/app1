<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Public;

use App\Domain\CourseCatalog\Models\Program;
use Illuminate\View\View;

class PublicProgramController
{
    public function show(string $slug): View
    {
        $program = Program::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['courses' => fn ($q) => $q->published()])
            ->firstOrFail();

        return view('public.programs.show', compact('program'));
    }
}
