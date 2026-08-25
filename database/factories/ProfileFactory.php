<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'country_code' => fake()->randomElement(['NG', 'KE', 'ZA', 'GH', 'TZ', 'UG', 'SN', 'ET', 'RW', 'CM']),
            'language_code' => fake()->randomElement(['en', 'fr', 'sw', 'pt', 'ha', 'yo', 'am', 'zu', 'am']),
            'onboarding_completed' => fake()->boolean(70),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed' => false,
        ]);
    }
}
