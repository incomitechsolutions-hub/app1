<?php

use App\Domain\Taxonomy\Models\DifficultyLevel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DifficultyLevel::query()->firstOrCreate(
            ['code' => 'expert'],
            ['label' => 'Experte', 'sort_order' => 30]
        );
    }

    public function down(): void
    {
        DifficultyLevel::query()->where('code', 'expert')->delete();
    }
};
