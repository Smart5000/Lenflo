<?php

namespace Database\Factories\Content;

use App\Enums\ModerationStatus;
use App\Models\Content\ContentItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentItem>
 */
class ContentItemFactory extends Factory
{
    protected $model = ContentItem::class;

    public function definition(): array
    {
        $videoId = fake()->uuid();

        return [
            'provider' => 'youtube',
            'external_id' => $videoId,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'thumbnail_url' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
            'embed_url' => "https://www.youtube.com/embed/{$videoId}",
            'duration_seconds' => fake()->numberBetween(30, 600),
            'original_language_code' => fake()->randomElement(['en', 'fr', 'sw', 'pt']),
            'country_code' => fake()->randomElement(['NG', 'KE', 'ZA', 'GH']),
            'moderation_status' => ModerationStatus::Candidate->value,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
            'internal_notes' => null,
            'editorial_score' => null,
            'published_at' => null,
        ];
    }

    public function candidate(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::Candidate->value,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::PendingReview->value,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::Approved->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::Published->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'published_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::Rejected->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::Disabled->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function feedEligible(): static
    {
        return $this->approved();
    }
}
