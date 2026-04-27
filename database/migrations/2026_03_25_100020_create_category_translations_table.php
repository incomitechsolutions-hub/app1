<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('locale_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('category_id', 'cattr_category_fk')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->foreign('locale_id', 'cattr_locale_fk')
                ->references('id')
                ->on('locales')
                ->cascadeOnDelete();
            $table->unique(['category_id', 'locale_id'], 'cattr_category_locale_uq');
            $table->unique(['locale_id', 'slug'], 'cattr_locale_slug_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_translations');
    }
};
