<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\Program;
use Illuminate\Support\Facades\DB;

class ProgramService
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncCourses(Program $program, array $rows): void
    {
        $sync = [];
        foreach ($rows as $index => $row) {
            $cid = (int) ($row['course_id'] ?? 0);
            if ($cid < 1) {
                continue;
            }
            $sync[$cid] = ['sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index];
        }
        $program->courses()->sync($sync);
    }

    public function store(array $data): Program
    {
        return DB::transaction(function () use ($data) {
            $rows = $data['program_courses'] ?? [];
            unset($data['program_courses']);
            $program = Program::query()->create($data);
            $this->syncCourses($program, is_array($rows) ? $rows : []);

            return $program->load('courses');
        });
    }

    public function update(Program $program, array $data): Program
    {
        return DB::transaction(function () use ($program, $data) {
            $rows = $data['program_courses'] ?? [];
            unset($data['program_courses']);
            $program->fill($data);
            $program->save();
            $this->syncCourses($program, is_array($rows) ? $rows : []);

            return $program->load('courses');
        });
    }

    public function delete(Program $program): void
    {
        $program->delete();
    }
}
