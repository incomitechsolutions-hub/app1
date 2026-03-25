<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_open_classrooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->decimal('duration_hours', 8, 2)->nullable();
            $table->string('location_label', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_open_classrooms');
    }
};
