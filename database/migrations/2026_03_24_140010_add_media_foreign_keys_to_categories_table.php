<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('icon_media_asset_id')->nullable()->after('sort_order')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('header_media_asset_id')->nullable()->after('icon_media_asset_id')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('header_media_asset_id');
            $table->dropConstrainedForeignId('icon_media_asset_id');
        });
    }
};
