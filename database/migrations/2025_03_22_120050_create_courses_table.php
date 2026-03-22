<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->decimal('duration_hours', 8, 2)->nullable();
            $table->string('language_code', 16)->default('de');
            $table->string('status')->default('draft');
            $table->foreignId('primary_category_id')->nullable()->constrained('categories');
            $table->foreignId('difficulty_level_id')->nullable()->constrained('difficulty_levels');
            $table->foreignId('hero_media_asset_id')->nullable()->constrained('media_assets');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
