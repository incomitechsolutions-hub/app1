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
            $table->unsignedBigInteger('ai_course_generation_session_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type', 64);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('ai_course_generation_session_id', 'acge_session_fk')
                ->references('id')
                ->on('ai_course_generation_sessions')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'acge_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['ai_course_generation_session_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_course_generation_events');
    }
};
