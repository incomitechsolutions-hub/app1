<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_course_generation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ai_course_generation_session_id')->constrained('ai_course_generation_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 64);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['ai_course_generation_session_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_course_generation_events');
    }
};
