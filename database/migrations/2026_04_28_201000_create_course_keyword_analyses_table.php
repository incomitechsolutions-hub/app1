<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_keyword_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('topic');
            $table->json('subtopics')->nullable();
            $table->json('raw_google_response')->nullable();
            $table->json('raw_ai_response')->nullable();
            $table->string('selected_primary_keyword')->nullable();
            $table->json('selected_keywords')->nullable();
            $table->json('selected_clusters')->nullable();
            $table->unsignedTinyInteger('seo_opportunity_score')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('course_id', 'cka_course_idx');
            $table->index('created_by', 'cka_created_by_idx');
            $table->foreign('course_id', 'cka_course_fk')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('created_by', 'cka_user_fk')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_keyword_analyses');
    }
};

