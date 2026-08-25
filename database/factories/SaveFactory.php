<?php

namespace Database\Factories;

use App\Models\Content\ContentItem;
use App\Models\Save;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Save>
 */
class SaveFactory extends Factory
{
    protected $model = Save::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content_item_id' => ContentItem::factory(),
        ];
    }
}
