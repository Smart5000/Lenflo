<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'NG', 'name' => 'Nigeria', 'is_active' => true],
            ['code' => 'KE', 'name' => 'Kenya', 'is_active' => true],
            ['code' => 'ZA', 'name' => 'South Africa', 'is_active' => true],
            ['code' => 'GH', 'name' => 'Ghana', 'is_active' => true],
            ['code' => 'TZ', 'name' => 'Tanzania', 'is_active' => true],
            ['code' => 'UG', 'name' => 'Uganda', 'is_active' => true],
            ['code' => 'SN', 'name' => 'Senegal', 'is_active' => true],
            ['code' => 'ET', 'name' => 'Ethiopia', 'is_active' => true],
            ['code' => 'RW', 'name' => 'Rwanda', 'is_active' => true],
            ['code' => 'CM', 'name' => 'Cameroon', 'is_active' => true],
            ['code' => 'CI', 'name' => 'Ivory Coast', 'is_active' => true],
            ['code' => 'ML', 'name' => 'Mali', 'is_active' => true],
            ['code' => 'BF', 'name' => 'Burkina Faso', 'is_active' => true],
            ['code' => 'NE', 'name' => 'Niger', 'is_active' => true],
            ['code' => 'MG', 'name' => 'Madagascar', 'is_active' => true],
            ['code' => 'MZ', 'name' => 'Mozambique', 'is_active' => true],
            ['code' => 'ZM', 'name' => 'Zambia', 'is_active' => true],
            ['code' => 'ZW', 'name' => 'Zimbabwe', 'is_active' => true],
            ['code' => 'MW', 'name' => 'Malawi', 'is_active' => true],
            ['code' => 'SD', 'name' => 'Sudan', 'is_active' => true],
        ];

        DB::table('countries')->insert(
            array_map(fn ($c) => [...$c, 'created_at' => now(), 'updated_at' => now()], $countries)
        );
    }
}
