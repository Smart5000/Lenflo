<?php

namespace Database\Factories;

use App\Enums\EngagementType;
use App\Models\Content\ContentItem;
use App\Models\EngagementEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EngagementEvent>
 */
class EngagementEventFactory extends Factory
{
    protected $model = EngagementEvent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content_item_id' => ContentItem::factory(),
            'type' => EngagementType::Impression,
            'watch_seconds' => null,
            'metadata' => null,
        ];
    }

    public function impression(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EngagementType::Impression,
        ]);
    }

    public function playStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EngagementType::PlayStart,
        ]);
    }

    public function completion(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EngagementType::Completion,
        ]);
    }

    public function skip(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EngagementType::Skip,
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
