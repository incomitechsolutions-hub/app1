<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            if (Schema::hasColumn('courses', 'delivery_mode')) {
                $table->dropColumn('delivery_mode');
            }
            if (Schema::hasColumn('courses', 'duration_hours')) {
                $table->dropColumn('duration_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            if (! Schema::hasColumn('courses', 'delivery_mode')) {
                $table->string('delivery_mode', 32)->nullable()->after('delivery_format');
            }
            if (! Schema::hasColumn('courses', 'duration_hours')) {
                $table->decimal('duration_hours', 8, 2)->nullable();
            }
        });
    }
};
