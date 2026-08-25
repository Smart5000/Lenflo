<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CountrySeeder::class, LanguageSeeder::class]);
    }

    public function test_onboarding_page_requires_authentication(): void
    {
        $response = $this->get(route('onboarding'));

        $response->assertRedirect(route('login'));
    }

    public function test_onboarding_page_is_rendered_for_incomplete_profile(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('onboarding'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('onboarding/index'));
    }

    public function test_onboarding_completed_user_is_redirected_to_feed(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('onboarding'));

        $response->assertRedirect(route('feed'));
    }

    public function test_onboarding_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), []);

        $response->assertSessionHasErrors(['birth_date', 'country_code', 'language_code']);
    }

    public function test_onboarding_store_validates_birth_date(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'birth_date' => '2024-01-01',
            'country_code' => 'NG',
            'language_code' => 'en',
        ]);

        $response->assertSessionHasErrors('birth_date');
    }

    public function test_onboarding_store_validates_country_exists(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'birth_date' => '2000-01-15',
            'country_code' => 'ZZ',
            'language_code' => 'en',
        ]);

        $response->assertSessionHasErrors('country_code');
    }

    public function test_onboarding_store_completes_onboarding(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'birth_date' => '2000-01-15',
            'country_code' => 'NG',
            'language_code' => 'en',
        ]);

        $response->assertRedirect(route('feed'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'birth_date' => '2000-01-15 00:00:00',
        ]);

        $profile = $user->fresh()->profile;
        $this->assertTrue($profile->onboarding_completed);
        $this->assertEquals('NG', $profile->country_code);
        $this->assertEquals('en', $profile->language_code);
    }

    public function test_dashboard_redirects_to_onboarding_when_incomplete(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding'));
    }

    public function test_settings_accessible_even_with_incomplete_onboarding(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_newly_registered_user_has_incomplete_profile(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user->profile);
        $this->assertFalse($user->profile->onboarding_completed);
    }
}
