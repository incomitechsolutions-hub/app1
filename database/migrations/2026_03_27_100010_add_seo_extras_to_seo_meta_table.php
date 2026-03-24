<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->string('focus_keyword')->nullable()->after('meta_description');
            $table->text('tags_csv')->nullable()->after('focus_keyword');
            $table->string('preview_image_url', 2048)->nullable()->after('tags_csv');
            $table->string('landing_page_url', 2048)->nullable()->after('preview_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn([
                'focus_keyword',
                'tags_csv',
                'preview_image_url',
                'landing_page_url',
            ]);
        });
    }
};
