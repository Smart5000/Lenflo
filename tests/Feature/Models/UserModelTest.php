<?php

namespace Tests\Feature\Models;

use App\Models\Content\ContentItem;
use App\Models\EngagementEvent;
use App\Models\Like;
use App\Models\Profile;
use App\Models\Save;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_profile(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'country_code' => 'NG',
            'language_code' => 'en',
        ]);

        $this->assertInstanceOf(Profile::class, $user->profile);
        $this->assertEquals('NG', $user->profile->country_code);
    }

    public function test_user_has_likes(): void
    {
        $user = User::factory()->create();
        $content = ContentItem::factory()->create();
        Like::factory()->create(['user_id' => $user->id, 'content_item_id' => $content->id]);

        $this->assertCount(1, $user->likes);
    }

    public function test_user_has_saves(): void
    {
        $user = User::factory()->create();
        $content = ContentItem::factory()->create();
        Save::factory()->create(['user_id' => $user->id, 'content_item_id' => $content->id]);

        $this->assertCount(1, $user->saves);
    }

    public function test_user_has_engagement_events(): void
    {
        $user = User::factory()->create();
        $content = ContentItem::factory()->create();
        EngagementEvent::factory()->create(['user_id' => $user->id, 'content_item_id' => $content->id]);

        $this->assertCount(1, $user->engagementEvents);
    }

    public function test_user_calculates_age(): void
    {
        $user = User::factory()->create(['birth_date' => now()->subYears(15)]);

        $this->assertEquals(15, $user->getAge());
    }

    public function test_user_returns_null_age_without_birth_date(): void
    {
        $user = User::factory()->create(['birth_date' => null]);

        $this->assertNull($user->getAge());
    }
}
