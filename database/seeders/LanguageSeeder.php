<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'am', 'name' => 'Amharic', 'is_active' => true],
            ['code' => 'ar', 'name' => 'Arabic', 'is_active' => true],
            ['code' => 'en', 'name' => 'English', 'is_active' => true],
            ['code' => 'fr', 'name' => 'French', 'is_active' => true],
            ['code' => 'ha', 'name' => 'Hausa', 'is_active' => true],
            ['code' => 'ig', 'name' => 'Igbo', 'is_active' => true],
            ['code' => 'pt', 'name' => 'Portuguese', 'is_active' => true],
            ['code' => 'rw', 'name' => 'Kinyarwanda', 'is_active' => true],
            ['code' => 'sw', 'name' => 'Swahili', 'is_active' => true],
            ['code' => 'yo', 'name' => 'Yoruba', 'is_active' => true],
            ['code' => 'zu', 'name' => 'Zulu', 'is_active' => true],
            ['code' => 'other', 'name' => 'Other', 'is_active' => true],
        ];

        DB::table('languages')->insert(
            array_map(fn ($l) => [...$l, 'created_at' => now(), 'updated_at' => now()], $languages)
        );
    }
}
