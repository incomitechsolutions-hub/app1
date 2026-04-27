<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('external_course_code')->nullable()->unique()->after('slug');
            $table->string('subtitle')->nullable()->after('title');
            $table->unsignedSmallInteger('duration_days')->nullable()->after('subtitle');
            $table->char('currency_code', 3)->default('EUR')->after('price');
            $table->string('delivery_mode', 32)->nullable()->after('delivery_format');
            $table->unsignedInteger('lessons_count')->nullable()->after('duration_days');
            $table->unsignedInteger('min_participants')->nullable()->after('lessons_count');
            $table->string('instructor_name')->nullable()->after('min_participants');
            $table->string('certificate_label')->nullable()->after('instructor_name');
            $table->string('author_name')->nullable()->after('published_at');
            $table->string('content_version', 32)->nullable()->after('author_name');
            $table->longText('ai_prompt_source')->nullable()->after('content_version');
            $table->longText('internal_notes')->nullable()->after('ai_prompt_source');
            $table->longText('target_audience_text')->nullable()->after('long_description');
            $table->longText('prerequisites_text')->nullable()->after('target_audience_text');
            $table->decimal('average_rating', 3, 2)->default(0)->after('internal_notes');
            $table->unsignedInteger('ratings_count')->default(0)->after('average_rating');
            $table->boolean('media_icon_enabled')->default(false)->after('ratings_count');
            $table->boolean('media_header_enabled')->default(false)->after('media_icon_enabled');
            $table->boolean('media_video_enabled')->default(false)->after('media_header_enabled');
            $table->boolean('media_gallery_enabled')->default(false)->after('media_video_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'external_course_code',
                'subtitle',
                'duration_days',
                'currency_code',
                'delivery_mode',
                'lessons_count',
                'min_participants',
                'instructor_name',
                'certificate_label',
                'author_name',
                'content_version',
                'ai_prompt_source',
                'internal_notes',
                'target_audience_text',
                'prerequisites_text',
                'average_rating',
                'ratings_count',
                'media_icon_enabled',
                'media_header_enabled',
                'media_video_enabled',
                'media_gallery_enabled',
            ]);
        });
    }
};
