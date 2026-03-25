<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_catalog_global_settings', function (Blueprint $table) {
            $table->decimal('default_list_price', 12, 2)->nullable()->after('tax_rate_percent');
        });
    }

    public function down(): void
    {
        Schema::table('course_catalog_global_settings', function (Blueprint $table) {
            $table->dropColumn('default_list_price');
        });
    }
};
