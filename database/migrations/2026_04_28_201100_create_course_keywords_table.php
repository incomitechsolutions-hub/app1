<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('analysis_id');
            $table->string('keyword');
            $table->string('type', 32)->nullable();
            $table->string('intent', 32)->nullable();
            $table->json('source')->nullable();
            $table->unsignedTinyInteger('relevance_score')->default(0);
            $table->unsignedTinyInteger('commercial_score')->default(0);
            $table->unsignedTinyInteger('course_fit_score')->default(0);
            $table->boolean('selected')->default(false);
            $table->timestamps();

            $table->index(['analysis_id', 'selected'], 'ck_analysis_selected_idx');
            $table->index('course_id', 'ck_course_idx');
            $table->foreign('analysis_id', 'ck_analysis_fk')->references('id')->on('course_keyword_analyses')->cascadeOnDelete();
            $table->foreign('course_id', 'ck_course_fk')->references('id')->on('courses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_keywords');
    }
};

