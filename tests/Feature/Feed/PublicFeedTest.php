<?php

namespace Tests\Feature\Feed;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_the_feed()
    {
        $response = $this->get(route('feed'));

        $response->assertOk();
    }

    public function test_authenticated_users_can_view_the_feed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('feed'));

        $response->assertOk();
    }

    public function test_feed_returns_inertia_response()
    {
        $response = $this->get(route('feed'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('feed/index'));
    }
}
