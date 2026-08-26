<?php

namespace Tests\Feature\Admin;

use App\Models\Content\ContentItem;
use App\Models\User;
use App\Services\YouTubeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YouTubeImportTest extends TestCase
{
    use RefreshDatabase;

    // ── Authorization tests ────────────────────────────────────

    public function test_guests_cannot_access_youtube_search(): void
    {
        $response = $this->get(route('admin.youtube.search'));
        $response->assertRedirect(route('login'));
    }

    public function test_normal_user_cannot_access_youtube_search(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->get(route('admin.youtube.search'));
        $response->assertForbidden();
    }

    public function test_super_admin_can_access_youtube_search(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        config(['services.youtube.api_key' => null]);

        $response = $this->get(route('admin.youtube.search'));
        $response->assertOk();
    }

    public function test_guests_cannot_import_youtube_video(): void
    {
        $response = $this->post(route('admin.youtube.import'), ['video_id' => 'dQw4w9WgXcQ']);
        $response->assertRedirect(route('login'));
    }

    public function test_normal_user_cannot_import_youtube_video(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->post(route('admin.youtube.import'), ['video_id' => 'dQw4w9WgXcQ']);
        $response->assertForbidden();
    }

    public function test_guests_cannot_import_by_url(): void
    {
        $response = $this->post(route('admin.content.import-url'), ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $response->assertRedirect(route('login'));
    }

    public function test_normal_user_cannot_import_by_url(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->post(route('admin.content.import-url'), ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $response->assertForbidden();
    }

    // ── Import from search tests ───────────────────────────────

    public function test_super_admin_can_import_from_search(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [[
                    'id' => 'dQw4w9WgXcQ',
                    'snippet' => [
                        'title' => 'Test Video Title',
                        'description' => 'A test video description.',
                        'channelTitle' => 'Test Channel',
                        'publishedAt' => '2024-01-15T10:00:00Z',
                        'defaultLanguage' => 'en',
                        'thumbnails' => [
                            'high' => ['url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg'],
                        ],
                    ],
                    'contentDetails' => [
                        'duration' => 'PT3M30S',
                    ],
                ]],
            ]),
        ]);

        $response = $this->post(route('admin.youtube.import'), ['video_id' => 'dQw4w9WgXcQ']);
        $response->assertRedirect();

        $this->assertDatabaseHas('content_items', [
            'provider' => 'youtube',
            'external_id' => 'dQw4w9WgXcQ',
            'title' => 'Test Video Title',
            'moderation_status' => 'candidate',
        ]);
    }

    public function test_imported_content_is_always_candidate(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [[
                    'id' => 'test1234567',
                    'snippet' => [
                        'title' => 'Candidate Test',
                        'description' => '',
                        'channelTitle' => 'Channel',
                        'publishedAt' => '2024-01-15T10:00:00Z',
                        'thumbnails' => [],
                    ],
                    'contentDetails' => [
                        'duration' => 'PT1M',
                    ],
                ]],
            ]),
        ]);

        $this->post(route('admin.youtube.import'), ['video_id' => 'test1234567']);

        $content = ContentItem::where('external_id', 'test1234567')->first();
        $this->assertNotNull($content);
        $this->assertEquals('candidate', $content->moderation_status);
        $this->assertNull($content->reviewed_by);
        $this->assertNull($content->published_at);
    }

    public function test_imported_content_has_correct_metadata(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [[
                    'id' => 'meta1234567',
                    'snippet' => [
                        'title' => 'Metadata Test Video',
                        'description' => 'Detailed description here.',
                        'channelTitle' => 'Education Channel',
                        'publishedAt' => '2024-06-01T00:00:00Z',
                        'defaultLanguage' => 'fr',
                        'thumbnails' => [
                            'high' => ['url' => 'https://img.youtube.com/vi/meta1234567/hqdefault.jpg'],
                        ],
                    ],
                    'contentDetails' => [
                        'duration' => 'PT1H5M10S',
                    ],
                ]],
            ]),
        ]);

        $this->post(route('admin.youtube.import'), ['video_id' => 'meta1234567']);

        $content = ContentItem::where('external_id', 'meta1234567')->first();
        $this->assertEquals('Metadata Test Video', $content->title);
        $this->assertEquals('Detailed description here.', $content->description);
        $this->assertEquals('https://www.youtube.com/embed/meta1234567', $content->embed_url);
        $this->assertEquals('https://img.youtube.com/vi/meta1234567/hqdefault.jpg', $content->thumbnail_url);
        $this->assertEquals(3910, $content->duration_seconds); // 1h 5m 10s
        $this->assertEquals('fr', $content->original_language_code);
        $this->assertEquals('youtube', $content->provider);
    }

    // ── Import by URL tests ────────────────────────────────────

    public function test_super_admin_can_import_by_url(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [[
                    'id' => 'url123456789',
                    'snippet' => [
                        'title' => 'URL Import Test',
                        'description' => 'Imported via URL.',
                        'channelTitle' => 'Channel',
                        'publishedAt' => '2024-01-15T10:00:00Z',
                        'thumbnails' => [],
                    ],
                    'contentDetails' => [
                        'duration' => 'PT2M',
                    ],
                ]],
            ]),
        ]);

        $response = $this->post(route('admin.content.import-url'), [
            'url' => 'https://www.youtube.com/watch?v=url123456789',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('content_items', [
            'provider' => 'youtube',
            'external_id' => 'url123456789',
            'title' => 'URL Import Test',
            'moderation_status' => 'candidate',
        ]);
    }

    public function test_invalid_youtube_url_is_rejected(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->post(route('admin.content.import-url'), [
            'url' => 'https://www.example.com/not-youtube',
        ]);
        $response->assertSessionHasErrors('url');
    }

    public function test_duplicate_video_import_returns_existing(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        config(['services.youtube.api_key' => 'test-key']);

        ContentItem::factory()->create([
            'provider' => 'youtube',
            'external_id' => 'dup12345678',
        ]);

        Http::fake([
            'googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [[
                    'id' => 'dup12345678',
                    'snippet' => [
                        'title' => 'Duplicate Video',
                        'description' => '',
                        'channelTitle' => 'Channel',
                        'publishedAt' => '2024-01-15T10:00:00Z',
                        'thumbnails' => [],
                    ],
                    'contentDetails' => [
                        'duration' => 'PT1M',
                    ],
                ]],
            ]),
        ]);

        $this->post(route('admin.youtube.import'), ['video_id' => 'dup12345678']);

        $this->assertEquals(1, ContentItem::where('external_id', 'dup12345678')->count());
    }

    // ── YouTubeService unit tests ──────────────────────────────

    public function test_youtube_service_extracts_video_id_from_watch_url(): void
    {
        $service = new YouTubeService;
        $this->assertEquals('dQw4w9WgXcQ', $service->extractVideoId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
    }

    public function test_youtube_service_extracts_video_id_from_short_url(): void
    {
        $service = new YouTubeService;
        $this->assertEquals('dQw4w9WgXcQ', $service->extractVideoId('https://youtu.be/dQw4w9WgXcQ'));
    }

    public function test_youtube_service_extracts_video_id_from_embed_url(): void
    {
        $service = new YouTubeService;
        $this->assertEquals('dQw4w9WgXcQ', $service->extractVideoId('https://www.youtube.com/embed/dQw4w9WgXcQ'));
    }

    public function test_youtube_service_extracts_video_id_from_shorts_url(): void
    {
        $service = new YouTubeService;
        $this->assertEquals('dQw4w9WgXcQ', $service->extractVideoId('https://www.youtube.com/shorts/dQw4w9WgXcQ'));
    }

    public function test_youtube_service_returns_null_for_invalid_url(): void
    {
        $service = new YouTubeService;
        $this->assertNull($service->extractVideoId('https://www.example.com/video'));
    }

    public function test_youtube_service_is_configured_when_key_present(): void
    {
        config(['services.youtube.api_key' => 'test-key']);
        $service = new YouTubeService;
        $this->assertTrue($service->isConfigured());
    }

    public function test_youtube_service_is_not_configured_when_key_missing(): void
    {
        config(['services.youtube.api_key' => null]);
        $service = new YouTubeService;
        $this->assertFalse($service->isConfigured());
    }
}
