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
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('locale_id');
            $table->string('title');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->timestamps();

            $table->foreign('course_id', 'ct_course_fk')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
            $table->foreign('locale_id', 'ct_locale_fk')
                ->references('id')
                ->on('locales')
                ->cascadeOnDelete();
            $table->unique(['course_id', 'locale_id'], 'ct_course_locale_uq');
            $table->unique(['locale_id', 'slug'], 'ct_locale_slug_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_translations');
    }
};
