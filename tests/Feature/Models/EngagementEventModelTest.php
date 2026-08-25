<?php

namespace Tests\Feature\Models;

use App\Enums\EngagementType;
use App\Models\Content\ContentItem;
use App\Models\EngagementEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementEventModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_event_belongs_to_user(): void
    {
        $event = EngagementEvent::factory()->create();

        $this->assertInstanceOf(User::class, $event->user);
    }

    public function test_engagement_event_belongs_to_content_item(): void
    {
        $event = EngagementEvent::factory()->create();

        $this->assertInstanceOf(ContentItem::class, $event->contentItem);
    }

    public function test_engagement_event_casts_type_enum(): void
    {
        $event = EngagementEvent::factory()->playStart()->create();

        $this->assertInstanceOf(EngagementType::class, $event->type);
        $this->assertEquals(EngagementType::PlayStart, $event->type);
    }

    public function test_engagement_event_allows_null_user(): void
    {
        $event = EngagementEvent::factory()->anonymous()->create();

        $this->assertNull($event->user_id);
    }

    public function test_engagement_event_metadata_is_array(): void
    {
        $event = EngagementEvent::factory()->create([
            'metadata' => ['position' => 3, 'source' => 'feed'],
        ]);

        $this->assertIsArray($event->metadata);
        $this->assertEquals(3, $event->metadata['position']);
    }
}
