<?php

namespace Database\Seeders;

use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\DifficultyLevel;
use App\Domain\Taxonomy\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ModuleStateSeeder::class);

        DefaultAdminSeeding::seed();

        Category::query()->firstOrCreate(
            ['slug' => 'allgemein'],
            [
                'name' => 'Allgemein',
                'description' => null,
                'parent_id' => null,
                'status' => 'published',
            ]
        );

        DifficultyLevel::query()->firstOrCreate(
            ['code' => 'beginner'],
            ['label' => 'Einsteiger', 'sort_order' => 10]
        );

        DifficultyLevel::query()->firstOrCreate(
            ['code' => 'advanced'],
            ['label' => 'Fortgeschritten', 'sort_order' => 20]
        );

        Tag::query()->firstOrCreate(
            ['slug' => 'fuehrung'],
            ['name' => 'Führung']
        );

        Audience::query()->firstOrCreate(
            ['slug' => 'fuehrungskraefte'],
            [
                'name' => 'Führungskräfte',
                'description' => null,
            ]
        );
    }
}
