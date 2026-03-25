<?php

namespace App\Domain\CourseCatalog\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Http\Requests\Admin\StoreProgramRequest;
use App\Domain\CourseCatalog\Http\Requests\Admin\UpdateProgramRequest;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\Program;
use App\Domain\CourseCatalog\Services\ProgramService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function __construct(
        private readonly ProgramService $programs
    ) {
        $this->authorizeResource(Program::class, 'program');
    }

    public function index(): View
    {
        $programs = Program::query()->orderBy('title')->paginate(30);

        return view('admin.programs.index', compact('programs'));
    }

    public function show(Program $program): RedirectResponse
    {
        return redirect()->route('admin.course-catalog.programs.edit', $program);
    }

    public function create(): View
    {
        return view('admin.programs.create', $this->formOptions());
    }

    public function store(StoreProgramRequest $request): RedirectResponse
    {
        $program = $this->programs->store($request->validated());

        return redirect()
            ->route('admin.course-catalog.programs.edit', $program)
            ->with('status', __('Programm angelegt.'));
    }

    public function edit(Program $program): View
    {
        $program->load('courses');

        return view('admin.programs.edit', array_merge(
            ['program' => $program],
            $this->formOptions()
        ));
    }

    public function update(UpdateProgramRequest $request, Program $program): RedirectResponse
    {
        $this->programs->update($program, $request->validated());

        return redirect()
            ->route('admin.course-catalog.programs.edit', $program)
            ->with('status', __('Programm gespeichert.'));
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->programs->delete($program);

        return redirect()
            ->route('admin.course-catalog.programs.index')
            ->with('status', __('Programm gelöscht.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
        ];
    }
}
