<?php

namespace Database\Seeders;

use App\Domain\Localization\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        Locale::query()->firstOrCreate(
            ['code' => 'de'],
            [
                'name' => 'Deutsch',
                'is_active' => true,
                'sort_order' => 10,
            ]
        );

        Locale::query()->firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'is_active' => true,
                'sort_order' => 20,
            ]
        );
    }
}
