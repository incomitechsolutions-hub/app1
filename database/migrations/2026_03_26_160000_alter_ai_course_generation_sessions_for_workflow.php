<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_course_generation_sessions', function (Blueprint $table): void {
            $table->json('placeholder_input')->nullable()->after('template_snapshot');
        });

        if (Schema::hasColumn('ai_course_generation_sessions', 'placeholder_values')) {
            foreach (DB::table('ai_course_generation_sessions')->get() as $row) {
                DB::table('ai_course_generation_sessions')
                    ->where('id', $row->id)
                    ->update(['placeholder_input' => $row->placeholder_values]);
            }
            Schema::table('ai_course_generation_sessions', function (Blueprint $table): void {
                $table->dropColumn('placeholder_values');
            });
        }

        Schema::table('ai_course_generation_sessions', function (Blueprint $table): void {
            $table->longText('full_prompt_audit')->nullable()->after('compiled_prompt');
            $table->json('draft_payload')->nullable()->after('full_prompt_audit');
            $table->json('confirmed_steps')->nullable()->after('draft_payload');
            $table->string('last_regenerated_section', 64)->nullable()->after('confirmed_steps');
            $table->foreignId('resulting_course_id')->nullable()->after('last_regenerated_section')->constrained('courses')->nullOnDelete();
            $table->text('last_error')->nullable()->after('resulting_course_id');
        });

        DB::table('ai_course_generation_sessions')->where('status', 'prompt_ready')->update(['status' => 'in_review']);
    }

    public function down(): void
    {
        Schema::table('ai_course_generation_sessions', function (Blueprint $table): void {
            $table->dropForeign(['resulting_course_id']);
            $table->dropColumn([
                'full_prompt_audit',
                'draft_payload',
                'confirmed_steps',
                'last_regenerated_section',
                'resulting_course_id',
                'last_error',
            ]);
        });

        Schema::table('ai_course_generation_sessions', function (Blueprint $table): void {
            $table->json('placeholder_values')->nullable();
        });

        if (Schema::hasColumn('ai_course_generation_sessions', 'placeholder_input')) {
            foreach (DB::table('ai_course_generation_sessions')->get() as $row) {
                DB::table('ai_course_generation_sessions')
                    ->where('id', $row->id)
                    ->update(['placeholder_values' => $row->placeholder_input ?? json_encode([])]);
            }
            Schema::table('ai_course_generation_sessions', function (Blueprint $table): void {
                $table->dropColumn('placeholder_input');
            });
        }

        DB::table('ai_course_generation_sessions')->where('status', 'in_review')->update(['status' => 'prompt_ready']);
    }
};
