<?php

namespace App\Services\Admin;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminDashboardService
{
    /**
     * @return array{
     *     counts: array{
     *         courses: int,
     *         main_categories: int,
     *         sub_categories: int,
     *         inquiries: int,
     *         pages: int,
     *         published_courses: int
     *     },
     *     recent_activity: list<array{title: string, at: \Carbon\Carbon}>
     * }
     */
    public function getDashboardData(): array
    {
        return [
            'counts' => $this->counts(),
            'recent_activity' => $this->recentActivity(),
        ];
    }

    /**
     * @return array{courses: int, main_categories: int, sub_categories: int, inquiries: int, pages: int, published_courses: int}
     */
    private function counts(): array
    {
        $inquiries = 0;
        if (Schema::hasTable('inquiries')) {
            $inquiries = (int) DB::table('inquiries')->count();
        }

        $pages = 0;
        if (Schema::hasTable('pages')) {
            $pages = (int) DB::table('pages')->count();
        }

        return [
            'courses' => Course::query()->count(),
            'main_categories' => Category::query()->whereNull('parent_id')->count(),
            'sub_categories' => Category::query()->whereNotNull('parent_id')->count(),
            'inquiries' => $inquiries,
            'pages' => $pages,
            'published_courses' => Course::query()
                ->where('status', CourseStatus::Published)
                ->count(),
        ];
    }

    /**
     * @return list<array{title: string, at: \Carbon\Carbon}>
     */
    private function recentActivity(): array
    {
        if (! Schema::hasTable('inquiries') || ! Schema::hasColumn('inquiries', 'message')) {
            return [];
        }

        $rows = DB::table('inquiries')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['message', 'created_at']);

        $out = [];
        foreach ($rows as $row) {
            $snippet = is_string($row->message) && $row->message !== ''
                ? Str::limit(trim($row->message), 60)
                : 'Ohne Betreff';
            $out[] = [
                'title' => 'Neue Kontaktanfrage: '.$snippet,
                'at' => \Carbon\Carbon::parse($row->created_at),
            ];
        }

        return $out;
    }
}
