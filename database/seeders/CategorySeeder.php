<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'STEM', 'slug' => 'stem', 'description' => 'Science, Technology, Engineering, and Mathematics'],
            ['name' => 'Arts & Creativity', 'slug' => 'arts-creativity', 'description' => 'Visual arts, music, writing, and creative expression'],
            ['name' => 'Life Skills', 'slug' => 'life-skills', 'description' => 'Financial literacy, communication, and personal development'],
            ['name' => 'Languages', 'slug' => 'languages', 'description' => 'Language learning and literacy'],
            ['name' => 'Health & Wellness', 'slug' => 'health-wellness', 'description' => 'Physical and mental health education'],
            ['name' => 'Culture & History', 'slug' => 'culture-history', 'description' => 'African heritage, world cultures, and historical knowledge'],
            ['name' => 'Environment', 'slug' => 'environment', 'description' => 'Environmental awareness and sustainability'],
            ['name' => 'Entrepreneurship', 'slug' => 'entrepreneurship', 'description' => 'Business skills and innovation'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
