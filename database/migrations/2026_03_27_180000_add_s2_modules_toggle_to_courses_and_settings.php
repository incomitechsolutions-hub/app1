<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            if (! Schema::hasColumn('courses', 'is_s2_modules_enabled')) {
                $table->boolean('is_s2_modules_enabled')->default(false)->after('status');
            }
        });

        Schema::table('course_catalog_global_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('course_catalog_global_settings', 'default_s2_modules_enabled')) {
                $table->boolean('default_s2_modules_enabled')->default(false)->after('group_discount_layout');
            }
        });

        // Best-effort backfill for existing rows.
        if (Schema::hasColumn('course_catalog_global_settings', 'default_s2_modules_enabled')) {
            DB::table('course_catalog_global_settings')->whereNull('default_s2_modules_enabled')->update([
                'default_s2_modules_enabled' => false,
            ]);
        }

        if (Schema::hasColumn('courses', 'is_s2_modules_enabled')) {
            DB::table('courses')->whereNull('is_s2_modules_enabled')->update([
                'is_s2_modules_enabled' => false,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            if (Schema::hasColumn('courses', 'is_s2_modules_enabled')) {
                $table->dropColumn('is_s2_modules_enabled');
            }
        });

        Schema::table('course_catalog_global_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('course_catalog_global_settings', 'default_s2_modules_enabled')) {
                $table->dropColumn('default_s2_modules_enabled');
            }
        });
    }
};

