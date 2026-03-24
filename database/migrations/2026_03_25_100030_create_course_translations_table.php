<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'locale_id']);
            $table->unique(['locale_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_translations');
    }
};
