<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_course_generation_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ai_prompt_id')->nullable();
            $table->string('status', 32);
            $table->json('template_snapshot')->nullable();
            $table->json('placeholder_values');
            $table->text('brief');
            $table->longText('interpolated_body')->nullable();
            $table->longText('compiled_prompt');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'acgs_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('ai_prompt_id', 'acgs_prompt_fk')
                ->references('id')
                ->on('ai_prompts')
                ->nullOnDelete();
            $table->index(['user_id', 'status'], 'acgs_user_status_idx');
            $table->index('expires_at', 'acgs_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_course_generation_sessions');
    }
};

