<?php

namespace Database\Seeders;

use App\Enums\AgeBand;
use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\Content\ContentItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            LanguageSeeder::class,
            CategorySeeder::class,
        ]);

        // Admin user (local development only)
        $admin = null;

        if (app()->isLocal()) {
            $admin = User::factory()->superAdmin()->create([
                'name' => 'Super Admin',
                'email' => 'admin@lenflo.com',
                'birth_date' => '1990-01-15',
            ]);

            $admin->profile()->create([
                'country_code' => 'NG',
                'language_code' => 'en',
                'onboarding_completed' => true,
            ]);
        }

        // Test users
        $users = User::factory(5)->create();
        foreach ($users as $user) {
            $user->profile()->create([
                'country_code' => fake()->randomElement(['NG', 'KE', 'ZA', 'GH', 'TZ']),
                'language_code' => fake()->randomElement(['en', 'fr', 'sw']),
                'onboarding_completed' => fake()->boolean(80),
            ]);
        }

        // Content items
        $categories = Category::all()->take(6);
        $statuses = [
            ModerationStatus::Candidate,
            ModerationStatus::PendingReview,
            ModerationStatus::Approved,
            ModerationStatus::Published,
            ModerationStatus::Rejected,
        ];

        foreach (range(1, 20) as $i) {
            $status = fake()->randomElement($statuses);
            $content = ContentItem::factory()->create([
                'moderation_status' => $status->value,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => in_array($status, [ModerationStatus::Approved, ModerationStatus::Published, ModerationStatus::Rejected])
                    ? now()->subDays(fake()->numberBetween(0, 14))
                    : null,
                'published_at' => $status === ModerationStatus::Published
                    ? now()->subDays(fake()->numberBetween(0, 7))
                    : null,
                'editorial_score' => in_array($status, [ModerationStatus::Approved, ModerationStatus::Published])
                    ? fake()->randomFloat(2, 3.0, 5.0)
                    : null,
                'rejection_reason' => $status === ModerationStatus::Rejected
                    ? fake()->sentence()
                    : null,
            ]);

            // Attach random categories
            $content->categories()->attach(
                $categories->random(fake()->numberBetween(1, 3))->pluck('id')
            );

            // Attach random age bands
            $bands = fake()->randomElements(
                array_column(AgeBand::cases(), 'value'),
                fake()->numberBetween(1, 3)
            );
            $content->assignAgeBands($bands);
        }
    }
}
