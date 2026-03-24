<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternate_locales', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner');
            $table->string('locale_code', 16);
            $table->string('target_url');
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'locale_code'], 'alternate_locales_owner_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternate_locales');
    }
};
