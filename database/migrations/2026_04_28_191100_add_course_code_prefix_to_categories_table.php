<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('course_code_prefix', 24)->nullable()->after('slug');
        });

        $used = [];
        DB::table('categories')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$used): void {
                foreach ($rows as $row) {
                    $seed = strtoupper((string) ($row->slug ?? $row->name ?? 'CAT'));
                    $seed = preg_replace('/[^A-Z0-9]+/', '', $seed) ?? '';
                    $base = substr($seed !== '' ? $seed : 'CAT', 0, 18);
                    $candidate = $base;
                    $i = 1;

                    while ($candidate === '' || isset($used[$candidate]) || DB::table('categories')->where('course_code_prefix', $candidate)->exists()) {
                        $suffix = (string) $i;
                        $trimmed = substr($base, 0, max(1, 24 - strlen($suffix)));
                        $candidate = $trimmed.$suffix;
                        $i++;
                    }

                    DB::table('categories')
                        ->where('id', $row->id)
                        ->update(['course_code_prefix' => $candidate]);

                    $used[$candidate] = true;
                }
            });

        DB::statement('ALTER TABLE categories MODIFY course_code_prefix VARCHAR(24) NOT NULL');

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('course_code_prefix', 'categories_course_code_prefix_uq');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_course_code_prefix_uq');
            $table->dropColumn('course_code_prefix');
        });
    }
};
