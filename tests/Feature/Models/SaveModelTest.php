<?php

namespace Tests\Feature\Models;

use App\Models\Content\ContentItem;
use App\Models\Save;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_belongs_to_user(): void
    {
        $save = Save::factory()->create();

        $this->assertInstanceOf(User::class, $save->user);
    }

    public function test_save_belongs_to_content_item(): void
    {
        $save = Save::factory()->create();

        $this->assertInstanceOf(ContentItem::class, $save->contentItem);
    }

    public function test_save_unique_constraint(): void
    {
        $user = User::factory()->create();
        $content = ContentItem::factory()->create();

        Save::factory()->create([
            'user_id' => $user->id,
            'content_item_id' => $content->id,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        Save::factory()->create([
            'user_id' => $user->id,
            'content_item_id' => $content->id,
        ]);
    }
}
