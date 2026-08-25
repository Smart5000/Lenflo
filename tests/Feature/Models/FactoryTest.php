<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Content\ContentItem;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->birth_date);
        $this->assertNotNull($user->password);
    }

    public function test_user_factory_child_state(): void
    {
        $user = User::factory()->child()->create();

        $this->assertLessThan(13, $user->getAge());
    }

    public function test_user_factory_teen_state(): void
    {
        $user = User::factory()->teen()->create();

        $age = $user->getAge();
        $this->assertGreaterThanOrEqual(13, $age);
        $this->assertLessThanOrEqual(17, $age);
    }

    public function test_profile_factory_creates_valid_profile(): void
    {
        $profile = Profile::factory()->create();

        $this->assertNotNull($profile->country_code);
        $this->assertNotNull($profile->language_code);
        $this->assertIsBool($profile->onboarding_completed);
    }

    public function test_category_factory_creates_valid_category(): void
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->name);
        $this->assertNotNull($category->slug);
        $this->assertTrue($category->is_active);
    }

    public function test_content_item_factory_creates_valid_content(): void
    {
        $content = ContentItem::factory()->create();

        $this->assertEquals('youtube', $content->provider);
        $this->assertNotNull($content->external_id);
        $this->assertNotNull($content->title);
        $this->assertNotNull($content->embed_url);
        $this->assertStringContainsString($content->external_id, $content->embed_url);
    }

    public function test_content_item_factory_state_methods(): void
    {
        $candidate = ContentItem::factory()->candidate()->create();
        $this->assertEquals('candidate', $candidate->moderation_status);

        $pending = ContentItem::factory()->pending()->create();
        $this->assertEquals('pending_review', $pending->moderation_status);

        $approved = ContentItem::factory()->approved()->create();
        $this->assertEquals('approved', $approved->moderation_status);
        $this->assertNotNull($approved->reviewed_by);
        $this->assertNotNull($approved->reviewed_at);

        $published = ContentItem::factory()->published()->create();
        $this->assertEquals('published', $published->moderation_status);
        $this->assertNotNull($published->published_at);

        $rejected = ContentItem::factory()->rejected()->create();
        $this->assertEquals('rejected', $rejected->moderation_status);
        $this->assertNotNull($rejected->rejection_reason);

        $disabled = ContentItem::factory()->disabled()->create();
        $this->assertEquals('disabled', $disabled->moderation_status);
    }
}
