<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_categories')) {
            return;
        }

        $rows = DB::table('course_categories')->select('course_id', 'category_id')->orderBy('course_id')->get();
        foreach ($rows->groupBy('course_id') as $courseId => $cats) {
            $cid = (int) $courseId;
            $primary = DB::table('courses')->where('id', $cid)->value('primary_category_id');
            if ($primary) {
                continue;
            }
            $first = $cats->first();
            if ($first) {
                DB::table('courses')->where('id', $cid)->update(['primary_category_id' => $first->category_id]);
            }
        }

        Schema::drop('course_categories');
    }

    public function down(): void
    {
        Schema::create('course_categories', function ($table): void {
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['course_id', 'category_id']);
        });
    }
};
