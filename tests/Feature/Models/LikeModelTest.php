<?php

namespace Tests\Feature\Models;

use App\Models\Content\ContentItem;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_belongs_to_user(): void
    {
        $like = Like::factory()->create();

        $this->assertInstanceOf(User::class, $like->user);
    }

    public function test_like_belongs_to_content_item(): void
    {
        $like = Like::factory()->create();

        $this->assertInstanceOf(ContentItem::class, $like->contentItem);
    }

    public function test_like_unique_constraint(): void
    {
        $user = User::factory()->create();
        $content = ContentItem::factory()->create();

        Like::factory()->create([
            'user_id' => $user->id,
            'content_item_id' => $content->id,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        Like::factory()->create([
            'user_id' => $user->id,
            'content_item_id' => $content->id,
        ]);
    }
}
