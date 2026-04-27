<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure duration_hours exists (some environments may have dropped it in earlier migrations).
        if (! Schema::hasColumn('courses', 'duration_hours')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->decimal('duration_hours', 8, 2)->nullable()->after('published_at');
            });
        }

        if (Schema::hasColumn('courses', 'duration_days')) {
            // Backfill: 1 Tag = 7 Stunden
            DB::table('courses')->update([
                'duration_hours' => DB::raw('CASE WHEN duration_days IS NULL THEN NULL ELSE ROUND(duration_days * 7, 2) END'),
            ]);

            Schema::table('courses', function (Blueprint $table): void {
                $table->dropColumn('duration_days');
            });
        }
    }

    public function down(): void
    {
        // Recreate duration_days from duration_hours (best-effort).
        if (! Schema::hasColumn('courses', 'duration_days')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->unsignedSmallInteger('duration_days')->nullable();
            });
        }

        if (Schema::hasColumn('courses', 'duration_hours')) {
            DB::table('courses')->update([
                'duration_days' => DB::raw('CASE WHEN duration_hours IS NULL THEN NULL ELSE ROUND(duration_hours / 7) END'),
            ]);
        }

        // Keep duration_hours for rollback safety (some code may already be deployed).
    }
};

