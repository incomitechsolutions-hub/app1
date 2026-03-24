<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_catalog_global_settings', function (Blueprint $table) {
            $table->id();
            $table->char('default_currency', 3)->default('EUR');
            $table->string('default_delivery_format', 32)->default('presence');
            $table->string('default_language_code', 16)->default('de');
            $table->unsignedInteger('default_min_participants')->default(1);
            $table->decimal('tax_rate_percent', 5, 2)->default(19);
            $table->boolean('early_bird_enabled')->default(false);
            $table->unsignedInteger('early_bird_days_before')->nullable();
            $table->decimal('early_bird_discount_percent', 5, 2)->nullable();
            $table->boolean('group_discount_enabled')->default(false);
            $table->string('group_discount_layout', 32)->default('layout_2');
            $table->timestamps();
        });

        DB::table('course_catalog_global_settings')->insert([
            'id' => 1,
            'default_currency' => 'EUR',
            'default_delivery_format' => 'presence',
            'default_language_code' => 'de',
            'default_min_participants' => 3,
            'tax_rate_percent' => 19,
            'early_bird_enabled' => false,
            'early_bird_days_before' => 50,
            'early_bird_discount_percent' => 3,
            'group_discount_enabled' => false,
            'group_discount_layout' => 'layout_2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('course_catalog_global_settings');
    }
};
