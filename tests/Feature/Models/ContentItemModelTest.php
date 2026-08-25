<?php

namespace Tests\Feature\Models;

use App\Enums\AgeBand;
use App\Models\Category;
use App\Models\Content\ContentItem;
use App\Models\Like;
use App\Models\Save;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_item_belongs_to_reviewer(): void
    {
        $admin = User::factory()->create();
        $content = ContentItem::factory()->approved()->create([
            'reviewed_by' => $admin->id,
        ]);

        $this->assertEquals($admin->id, $content->reviewer->id);
    }

    public function test_content_item_has_categories(): void
    {
        $content = ContentItem::factory()->create();
        $category = Category::factory()->create();
        $content->categories()->attach($category);

        $this->assertCount(1, $content->categories);
        $this->assertEquals($category->id, $content->categories->first()->id);
    }

    public function test_content_item_has_likes(): void
    {
        $content = ContentItem::factory()->create();
        Like::factory()->create(['content_item_id' => $content->id]);

        $this->assertCount(1, $content->likes);
    }

    public function test_content_item_has_saves(): void
    {
        $content = ContentItem::factory()->create();
        Save::factory()->create(['content_item_id' => $content->id]);

        $this->assertCount(1, $content->saves);
    }

    public function test_feed_eligible_scope_returns_only_approved_and_published(): void
    {
        ContentItem::factory()->candidate()->count(2)->create();
        ContentItem::factory()->pending()->count(2)->create();
        ContentItem::factory()->approved()->count(3)->create();
        ContentItem::factory()->published()->count(2)->create();
        ContentItem::factory()->rejected()->count(1)->create();

        $eligible = ContentItem::feedEligible()->count();

        $this->assertEquals(5, $eligible);
    }

    public function test_pending_scope_returns_only_pending_review(): void
    {
        ContentItem::factory()->candidate()->count(2)->create();
        ContentItem::factory()->pending()->count(4)->create();
        ContentItem::factory()->approved()->count(3)->create();

        $pending = ContentItem::pending()->count();

        $this->assertEquals(4, $pending);
    }

    public function test_content_item_is_feed_eligible(): void
    {
        $approved = ContentItem::factory()->approved()->create();
        $published = ContentItem::factory()->published()->create();
        $candidate = ContentItem::factory()->candidate()->create();

        $this->assertTrue($approved->isFeedEligible());
        $this->assertTrue($published->isFeedEligible());
        $this->assertFalse($candidate->isFeedEligible());
    }

    public function test_content_item_unique_provider_external_id(): void
    {
        $content = ContentItem::factory()->create([
            'provider' => 'youtube',
            'external_id' => 'abc123',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        ContentItem::factory()->create([
            'provider' => 'youtube',
            'external_id' => 'abc123',
        ]);
    }

    public function test_content_item_age_bands_are_queryable(): void
    {
        $content = ContentItem::factory()->create();
        \DB::table('content_age_bands')->insert([
            ['content_item_id' => $content->id, 'age_band' => AgeBand::Teen->value],
            ['content_item_id' => $content->id, 'age_band' => AgeBand::AllAges->value],
        ]);

        $bands = $content->ageBandValues();

        $this->assertCount(2, $bands);
        $this->assertTrue($bands->contains(AgeBand::Teen->value));
    }
}
