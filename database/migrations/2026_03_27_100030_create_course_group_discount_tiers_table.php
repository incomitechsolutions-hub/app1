<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_group_discount_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_catalog_global_setting_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('min_participants');
            $table->decimal('discount_percent', 5, 2);
            $table->timestamps();

            $table->foreign('course_catalog_global_setting_id', 'cgdt_ccgs_fk')
                ->references('id')
                ->on('course_catalog_global_settings')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_group_discount_tiers');
    }
};
