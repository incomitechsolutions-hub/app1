<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_audiences', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('audience_id')->constrained('audiences')->cascadeOnDelete();
            $table->primary(['course_id', 'audience_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_audiences');
    }
};
