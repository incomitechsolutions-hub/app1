<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_taxonomy_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_new_category_status')->default('draft');
            $table->timestamps();
        });

        DB::table('category_taxonomy_settings')->insert([
            'id' => 1,
            'default_new_category_status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('category_taxonomy_settings');
    }
};
