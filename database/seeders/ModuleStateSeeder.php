<?php

namespace Database\Seeders;

use App\Models\ModuleState;
use Illuminate\Database\Seeder;

class ModuleStateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(config('modules', [])) as $moduleKey) {
            ModuleState::query()->firstOrCreate(
                ['module_key' => $moduleKey],
                ['enabled' => true]
            );
        }
    }
}
