<?php

namespace Tests\Feature\Feed;

use App\Enums\AgeBand;
use App\Enums\EngagementType;
use App\Models\Category;
use App\Models\Content\ContentItem;
use App\Models\EngagementEvent;
use App\Models\Like;
use App\Models\Save;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFeedTest extends TestCase
{
    use RefreshDatabase;

    // ── Basic access ──────────────────────────────────────────

    public function test_guests_can_view_the_feed(): void
    {
        $response = $this->get(route('feed'));
        $response->assertOk();
    }

    public function test_authenticated_users_can_view_the_feed(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('feed'));
        $response->assertOk();
    }

    public function test_feed_returns_inertia_response(): void
    {
        $response = $this->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('feed/index'));
    }

    // ── Content filtering ─────────────────────────────────────

    public function test_feed_includes_feed_eligible_content(): void
    {
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('contentItems', 1));
    }

    public function test_feed_excludes_non_eligible_content(): void
    {
        ContentItem::factory()->candidate()->create();
        ContentItem::factory()->rejected()->create();
        ContentItem::factory()->disabled()->create();

        $response = $this->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('contentItems', 0));
    }

    public function test_feed_excludes_content_without_age_bands(): void
    {
        ContentItem::factory()->approved()->create();

        $response = $this->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('contentItems', 0));
    }

    public function test_feed_returns_content_with_categories(): void
    {
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);
        $category = Category::factory()->create();
        $item->categories()->attach($category);

        $response = $this->get(route('feed'));
        $response->assertInertia(fn ($page) => $page
            ->has('contentItems.0.categories', 1)
            ->where('contentItems.0.categories.0.name', $category->name)
        );
    }

    // ── Age band filtering ────────────────────────────────────

    public function test_feed_filters_by_user_age_band(): void
    {
        $childContent = ContentItem::factory()->approved()->create();
        $childContent->assignAgeBands([AgeBand::EarlyLearner->value]);

        $adultContent = ContentItem::factory()->approved()->create();
        $adultContent->assignAgeBands([AgeBand::Adult->value]);

        $allAgesContent = ContentItem::factory()->approved()->create();
        $allAgesContent->assignAgeBands([AgeBand::AllAges->value]);

        $user = User::factory()->create(['birth_date' => now()->subYears(4)]);
        $user->profile()->create(['onboarding_completed' => true]);

        $response = $this->actingAs($user)->get(route('feed'));
        $response->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->has('contentItems', 2)
            ->where('contentItems.0.id', $childContent->id)
            ->where('contentItems.1.id', $allAgesContent->id)
        );
    }

    public function test_guest_sees_all_feed_eligible_content(): void
    {
        $childContent = ContentItem::factory()->approved()->create();
        $childContent->assignAgeBands([AgeBand::EarlyLearner->value]);

        $adultContent = ContentItem::factory()->approved()->create();
        $adultContent->assignAgeBands([AgeBand::Adult->value]);

        $response = $this->get(route('feed'));
        $response->assertOk();

        $response->assertInertia(fn ($page) => $page->has('contentItems', 2));
    }

    // ── User likes/saves in response ──────────────────────────

    public function test_authenticated_user_likes_are_included(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);
        Like::create(['user_id' => $user->id, 'content_item_id' => $item->id]);

        $response = $this->actingAs($user)->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has("userLikes.{$item->id}"));
    }

    public function test_authenticated_user_saves_are_included(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);
        Save::create(['user_id' => $user->id, 'content_item_id' => $item->id]);

        $response = $this->actingAs($user)->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has("userSaves.{$item->id}"));
    }

    public function test_guest_has_empty_likes_and_saves(): void
    {
        $response = $this->get(route('feed'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('userLikes', []));
    }

    // ── Like/save toggles ─────────────────────────────────────

    public function test_authenticated_user_can_like(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/like");
        $response->assertOk()->assertJson(['liked' => true, 'likes_count' => 1]);
        $this->assertDatabaseHas('likes', ['user_id' => $user->id, 'content_item_id' => $item->id]);
    }

    public function test_authenticated_user_can_unlike(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);
        Like::create(['user_id' => $user->id, 'content_item_id' => $item->id]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/like");
        $response->assertOk()->assertJson(['liked' => false, 'likes_count' => 0]);
        $this->assertDatabaseMissing('likes', ['user_id' => $user->id, 'content_item_id' => $item->id]);
    }

    public function test_authenticated_user_can_save(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/save");
        $response->assertOk()->assertJson(['saved' => true, 'saves_count' => 1]);
        $this->assertDatabaseHas('saves', ['user_id' => $user->id, 'content_item_id' => $item->id]);
    }

    public function test_authenticated_user_can_unsave(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);
        Save::create(['user_id' => $user->id, 'content_item_id' => $item->id]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/save");
        $response->assertOk()->assertJson(['saved' => false, 'saves_count' => 0]);
        $this->assertDatabaseMissing('saves', ['user_id' => $user->id, 'content_item_id' => $item->id]);
    }

    public function test_guest_cannot_like(): void
    {
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->postJson("/feed/{$item->id}/like");
        $response->assertUnauthorized();
    }

    public function test_guest_cannot_save(): void
    {
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->postJson("/feed/{$item->id}/save");
        $response->assertUnauthorized();
    }

    // ── Engagement events ─────────────────────────────────────

    public function test_like_creates_engagement_event(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $this->actingAs($user)->postJson("/feed/{$item->id}/like");

        $this->assertDatabaseHas('engagement_events', [
            'user_id' => $user->id,
            'content_item_id' => $item->id,
        ]);

        $event = EngagementEvent::where('user_id', $user->id)
            ->where('content_item_id', $item->id)
            ->first();
        $this->assertEquals(EngagementType::Like, $event->type);
    }

    public function test_save_creates_engagement_event(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $this->actingAs($user)->postJson("/feed/{$item->id}/save");

        $this->assertDatabaseHas('engagement_events', [
            'user_id' => $user->id,
            'content_item_id' => $item->id,
        ]);

        $event = EngagementEvent::where('user_id', $user->id)
            ->where('content_item_id', $item->id)
            ->first();
        $this->assertEquals(EngagementType::Save, $event->type);
    }

    // ── Share ──────────────────────────────────────────────────

    public function test_authenticated_user_can_share(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/share");
        $response->assertOk()->assertJson(['shared' => true]);
    }

    public function test_share_creates_engagement_event(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $this->actingAs($user)->postJson("/feed/{$item->id}/share");

        $event = EngagementEvent::where('user_id', $user->id)
            ->where('content_item_id', $item->id)
            ->where('type', EngagementType::Share)
            ->first();
        $this->assertNotNull($event);
    }

    public function test_guest_cannot_share(): void
    {
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $response = $this->postJson("/feed/{$item->id}/share");
        $response->assertUnauthorized();
    }

    public function test_shares_are_not_deduplicated(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        $this->actingAs($user)->postJson("/feed/{$item->id}/share");
        $this->actingAs($user)->postJson("/feed/{$item->id}/share");

        $this->assertEquals(2, EngagementEvent::where('user_id', $user->id)
            ->where('content_item_id', $item->id)
            ->where('type', EngagementType::Share)
            ->count());
    }

    // ── Duplicate prevention ───────────────────────────────────

    public function test_duplicate_likes_are_prevented_at_database_level(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        Like::create(['user_id' => $user->id, 'content_item_id' => $item->id]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/like");
        $response->assertOk()->assertJson(['liked' => false]);
        $this->assertDatabaseMissing('likes', ['user_id' => $user->id, 'content_item_id' => $item->id]);
    }

    public function test_duplicate_saves_are_prevented_at_database_level(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->approved()->create();
        $item->assignAgeBands([AgeBand::AllAges->value]);

        Save::create(['user_id' => $user->id, 'content_item_id' => $item->id]);

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/save");
        $response->assertOk()->assertJson(['saved' => false]);
        $this->assertDatabaseMissing('saves', ['user_id' => $user->id, 'content_item_id' => $item->id]);
    }

    // ── Authorization ──────────────────────────────────────────

    public function test_cannot_like_non_feed_eligible_content(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->candidate()->create();

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/like");
        $response->assertForbidden();
    }

    public function test_cannot_save_non_feed_eligible_content(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->candidate()->create();

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/save");
        $response->assertForbidden();
    }

    public function test_cannot_share_non_feed_eligible_content(): void
    {
        $user = User::factory()->create();
        $item = ContentItem::factory()->candidate()->create();

        $response = $this->actingAs($user)->postJson("/feed/{$item->id}/share");
        $response->assertForbidden();
    }
}
