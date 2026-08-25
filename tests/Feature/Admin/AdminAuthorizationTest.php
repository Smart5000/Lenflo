<?php

namespace Tests\Feature\Admin;

use App\Models\Content\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page_when_accessing_admin()
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_admin_role_is_forbidden_from_admin()
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertForbidden();
    }

    public function test_super_admin_can_access_admin_dashboard()
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertOk();
    }

    public function test_guests_are_redirected_to_the_login_page_when_accessing_admin_content()
    {
        $response = $this->get('/admin/content');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_admin_role_is_forbidden_from_admin_content()
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $response = $this->get('/admin/content');
        $response->assertForbidden();
    }

    public function test_super_admin_can_access_admin_content_listing()
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        ContentItem::factory()->count(3)->create();

        $response = $this->get('/admin/content');
        $response->assertOk();
    }

    public function test_super_admin_can_access_admin_content_detail()
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $content = ContentItem::factory()->create();

        $response = $this->get("/admin/content/{$content->id}");
        $response->assertOk();
    }

    public function test_user_with_non_admin_role_is_forbidden_from_admin_content_detail()
    {
        $user = User::factory()->create(['role' => 'editor']);
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $content = ContentItem::factory()->create();

        $response = $this->get("/admin/content/{$content->id}");
        $response->assertForbidden();
    }

    public function test_admin_gate_denies_users_with_null_role()
    {
        $user = User::factory()->create(['role' => null]);
        $this->actingAs($user);

        $this->assertFalse($user->can('admin'));
    }

    public function test_admin_gate_allows_super_admin()
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $this->assertTrue($user->can('admin'));
    }

    public function test_is_super_admin_method_returns_true_for_super_admin()
    {
        $user = User::factory()->superAdmin()->create();
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_is_super_admin_method_returns_false_for_regular_user()
    {
        $user = User::factory()->create(['role' => null]);
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_super_admin_method_returns_false_for_non_admin_role()
    {
        $user = User::factory()->create(['role' => 'editor']);
        $this->assertFalse($user->isSuperAdmin());
    }
}
