<?php

namespace Database\Seeders;

use App\Domain\Localization\Models\Locale;
use App\Domain\Localization\Models\Market;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $de = Locale::query()->where('code', 'de')->first();
        $en = Locale::query()->where('code', 'en')->first();

        $rows = [
            ['country_code' => 'DE', 'display_code' => 'DE', 'name' => 'Deutschland', 'domain' => 'example.de', 'vat_rate' => 19.00, 'is_active' => true, 'default_locale_id' => $de?->id],
            ['country_code' => 'AT', 'display_code' => 'AT', 'name' => 'Österreich', 'domain' => 'example.at', 'vat_rate' => 21.00, 'is_active' => true, 'default_locale_id' => $de?->id],
            ['country_code' => 'CH', 'display_code' => 'CH', 'name' => 'Schweiz', 'domain' => 'example.ch', 'vat_rate' => 8.10, 'is_active' => false, 'default_locale_id' => $de?->id],
            ['country_code' => null, 'display_code' => 'EN', 'name' => 'Englisch', 'domain' => 'example.com', 'vat_rate' => 5.00, 'is_active' => true, 'default_locale_id' => $en?->id ?? $de?->id],
        ];

        foreach ($rows as $index => $row) {
            Market::query()->updateOrCreate(
                ['domain' => $row['domain']],
                array_merge($row, ['sort_order' => $index * 10])
            );
        }
    }
}
