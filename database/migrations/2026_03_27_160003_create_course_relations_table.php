<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_relations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('related_course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('relation_type', 32)->default('follow_up');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'related_course_id', 'relation_type'], 'course_relations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_relations');
    }
};
