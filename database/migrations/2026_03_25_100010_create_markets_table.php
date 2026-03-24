<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->nullable();
            $table->string('display_code', 8);
            $table->string('name');
            $table->string('domain')->unique();
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('default_locale_id')->nullable()->constrained('locales')->nullOnDelete();
            $table->foreignId('flag_media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
