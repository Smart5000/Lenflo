<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => 'test-client-id']);
        config(['services.google.client_secret' => 'test-client-secret']);
        config(['services.google.redirect' => 'http://localhost/auth/google/callback']);
    }

    public function test_google_redirect_route_returns_redirect_to_google(): void
    {
        $response = $this->get(route('google.redirect'));

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com', $location);
        $this->assertStringContainsString('client_id=test-client-id', $location);
        $this->assertStringContainsString('scope=', $location);
    }

    public function test_google_callback_with_invalid_state_shows_error(): void
    {
        $response = $this->get(route('google.callback', [
            'code' => 'test-code',
            'state' => 'invalid-state',
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('google');
    }

    public function test_google_callback_without_code_shows_error(): void
    {
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $response = $this->get(route('google.callback', [
            'state' => $state,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('google');
    }

    public function test_google_callback_creates_new_user_and_redirects_to_onboarding(): void
    {
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'fake-access-token',
            ]),
            'www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => 'google-123',
                'email' => 'newuser@gmail.com',
                'name' => 'New User',
                'verified_email' => true,
            ]),
        ]);

        $response = $this->get(route('google.callback', [
            'code' => 'valid-code',
            'state' => $state,
        ]));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@gmail.com',
            'name' => 'New User',
        ]);

        $user = User::where('email', 'newuser@gmail.com')->first();
        $this->assertNotNull($user->profile);
        $this->assertFalse($user->profile->onboarding_completed);

        $response->assertRedirect(route('onboarding'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_logs_in_existing_user(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@gmail.com',
        ]);

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'fake-access-token',
            ]),
            'www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => 'google-123',
                'email' => 'existing@gmail.com',
                'name' => 'Existing User',
                'verified_email' => true,
            ]),
        ]);

        $response = $this->get(route('google.callback', [
            'code' => 'valid-code',
            'state' => $state,
        ]));

        $this->assertAuthenticatedAs($existingUser);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_google_callback_with_api_failure_shows_error(): void
    {
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([], 400),
        ]);

        $response = $this->get(route('google.callback', [
            'code' => 'valid-code',
            'state' => $state,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('google');
    }

    public function test_user_with_incomplete_onboarding_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding'));
    }

    public function test_user_with_completed_onboarding_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }
}
