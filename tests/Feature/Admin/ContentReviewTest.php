<?php

namespace Tests\Feature\Admin;

use App\Enums\AgeBand;
use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\Content\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentReviewTest extends TestCase
{
    use RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────

    public function test_guest_cannot_access_review_page(): void
    {
        $content = ContentItem::factory()->create();
        $this->get(route('admin.content.review', $content))->assertRedirect(route('login'));
    }

    public function test_normal_user_cannot_access_review_page(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $this->get(route('admin.content.review', $content))->assertForbidden();
    }

    public function test_super_admin_can_access_review_page(): void
    {
        $user = User::factory()->superAdmin()->create();
        $user->profile()->create(['onboarding_completed' => true]);
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $this->get(route('admin.content.review', $content))->assertOk();
    }

    public function test_normal_user_cannot_approve(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $this->post(route('admin.content.approve', $content))->assertForbidden();
    }

    public function test_normal_user_cannot_reject(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $this->post(route('admin.content.reject', $content), ['rejection_reason' => 'No'])->assertForbidden();
    }

    public function test_normal_user_cannot_publish(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->approved()->create();
        $this->post(route('admin.content.publish', $content))->assertForbidden();
    }

    public function test_normal_user_cannot_disable(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->published()->create();
        $this->post(route('admin.content.disable', $content))->assertForbidden();
    }

    public function test_normal_user_cannot_update_classification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $category = Category::factory()->create();

        $this->put(route('admin.content.classification', $content), [
            'category_ids' => [$category->id],
            'age_bands' => [AgeBand::AllAges->value],
        ])->assertForbidden();
    }

    // ── State transitions: approve ────────────────────────────

    public function test_super_admin_can_approve_candidate(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->candidate()->create();
        $this->post(route('admin.content.approve', $content))->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Approved->value, $content->moderation_status);
        $this->assertEquals($user->id, $content->reviewed_by);
        $this->assertNotNull($content->reviewed_at);
        $this->assertNull($content->rejection_reason);
    }

    // ── State transitions: reject ─────────────────────────────

    public function test_super_admin_can_reject_candidate(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->candidate()->create();
        $this->post(route('admin.content.reject', $content), [
            'rejection_reason' => 'Inappropriate content',
        ])->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Rejected->value, $content->moderation_status);
        $this->assertEquals($user->id, $content->reviewed_by);
        $this->assertNotNull($content->reviewed_at);
        $this->assertEquals('Inappropriate content', $content->rejection_reason);
    }

    public function test_rejection_requires_reason(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->candidate()->create();
        $this->post(route('admin.content.reject', $content), [])->assertSessionHasErrors('rejection_reason');
    }

    // ── State transitions: publish ────────────────────────────

    public function test_super_admin_can_publish_approved(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->approved()->create();
        $this->post(route('admin.content.publish', $content))->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Published->value, $content->moderation_status);
        $this->assertNotNull($content->published_at);
    }

    // ── State transitions: disable ────────────────────────────

    public function test_super_admin_can_disable_published(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->published()->create();
        $this->post(route('admin.content.disable', $content))->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Disabled->value, $content->moderation_status);
    }

    public function test_super_admin_can_disable_approved(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->approved()->create();
        $this->post(route('admin.content.disable', $content))->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Disabled->value, $content->moderation_status);
    }

    // ── State transitions: re-review ──────────────────────────

    public function test_rejected_content_can_be_reset_to_candidate(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->rejected()->create();
        $this->post(route('admin.content.re-review', $content))->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Candidate->value, $content->moderation_status);
        $this->assertNull($content->rejection_reason);
    }

    public function test_disabled_content_can_be_reset_to_candidate(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->disabled()->create();
        $this->post(route('admin.content.re-review', $content))->assertRedirect();

        $content->refresh();
        $this->assertEquals(ModerationStatus::Candidate->value, $content->moderation_status);
    }

    // ── Classification ────────────────────────────────────────

    public function test_super_admin_can_update_classification(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        $this->put(route('admin.content.classification', $content), [
            'category_ids' => [$cat1->id, $cat2->id],
            'age_bands' => [AgeBand::Teen->value, AgeBand::AllAges->value],
            'original_language_code' => 'sw',
            'country_code' => 'KE',
            'internal_notes' => 'Great educational content.',
        ])->assertRedirect();

        $content->refresh();
        $this->assertEquals(['sw'], [$content->original_language_code]);
        $this->assertEquals('KE', $content->country_code);
        $this->assertEquals('Great educational content.', $content->internal_notes);
        $this->assertEqualsCanonicalizing([$cat1->id, $cat2->id], $content->categories()->pluck('content_categories.category_id')->all());
        $this->assertEqualsCanonicalizing(
            [AgeBand::Teen->value, AgeBand::AllAges->value],
            $content->ageBandValues()->all()
        );
    }

    public function test_classification_requires_age_bands(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $category = Category::factory()->create();

        $this->put(route('admin.content.classification', $content), [
            'category_ids' => [$category->id],
            'age_bands' => [],
        ])->assertSessionHasErrors('age_bands');
    }

    public function test_classification_replaces_categories(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();
        $cat3 = Category::factory()->create();

        $content->categories()->attach([$cat1->id, $cat2->id]);

        $this->put(route('admin.content.classification', $content), [
            'category_ids' => [$cat2->id, $cat3->id],
            'age_bands' => [AgeBand::AllAges->value],
        ]);

        $content->refresh();
        $this->assertEqualsCanonicalizing([$cat2->id, $cat3->id], $content->categories()->pluck('categories.id')->all());
    }

    public function test_classification_replaces_age_bands(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->create();
        $category = Category::factory()->create();

        $content->assignAgeBands([AgeBand::EarlyLearner->value, AgeBand::Teen->value]);

        $this->put(route('admin.content.classification', $content), [
            'category_ids' => [$category->id],
            'age_bands' => [AgeBand::Adult->value],
        ]);

        $content->refresh();
        $this->assertEquals([AgeBand::Adult->value], $content->ageBandValues()->all());
    }

    // ── Feed eligibility ──────────────────────────────────────

    public function test_candidate_is_not_feed_eligible(): void
    {
        $content = ContentItem::factory()->candidate()->create();
        $this->assertFalse($content->fresh()->isFeedEligible());
    }

    public function test_approved_without_age_bands_is_not_feed_eligible(): void
    {
        $content = ContentItem::factory()->approved()->create();
        $this->assertFalse($content->fresh()->isFeedEligible());
    }

    public function test_approved_with_age_bands_is_feed_eligible(): void
    {
        $content = ContentItem::factory()->approved()->create();
        $content->assignAgeBands([AgeBand::AllAges->value]);
        $this->assertTrue($content->fresh()->isFeedEligible());
    }

    public function test_published_without_age_bands_is_not_feed_eligible(): void
    {
        $content = ContentItem::factory()->published()->create();
        $this->assertFalse($content->fresh()->isFeedEligible());
    }

    public function test_published_with_age_bands_is_feed_eligible(): void
    {
        $content = ContentItem::factory()->published()->create();
        $content->assignAgeBands([AgeBand::Teen->value]);
        $this->assertTrue($content->fresh()->isFeedEligible());
    }

    public function test_rejected_is_not_feed_eligible(): void
    {
        $content = ContentItem::factory()->rejected()->create();
        $content->assignAgeBands([AgeBand::AllAges->value]);
        $this->assertFalse($content->fresh()->isFeedEligible());
    }

    public function test_disabled_is_not_feed_eligible(): void
    {
        $content = ContentItem::factory()->disabled()->create();
        $content->assignAgeBands([AgeBand::AllAges->value]);
        $this->assertFalse($content->fresh()->isFeedEligible());
    }

    public function test_feed_eligible_scope_requires_age_bands(): void
    {
        $withBands = ContentItem::factory()->approved()->create();
        $withBands->assignAgeBands([AgeBand::AllAges->value]);

        $withoutBands = ContentItem::factory()->approved()->create();

        $eligible = ContentItem::feedEligible()->pluck('id')->all();
        $this->assertContains($withBands->id, $eligible);
        $this->assertNotContains($withoutBands->id, $eligible);
    }

    // ── Audit fields ──────────────────────────────────────────

    public function test_approve_sets_audit_fields(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->candidate()->create();
        $this->post(route('admin.content.approve', $content));

        $content->refresh();
        $this->assertEquals($user->id, $content->reviewed_by);
        $this->assertNotNull($content->reviewed_at);
        $this->assertNull($content->published_at);
    }

    public function test_reject_sets_audit_fields_and_reason(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->candidate()->create();
        $this->post(route('admin.content.reject', $content), [
            'rejection_reason' => 'Misleading title',
        ]);

        $content->refresh();
        $this->assertEquals($user->id, $content->reviewed_by);
        $this->assertNotNull($content->reviewed_at);
        $this->assertEquals('Misleading title', $content->rejection_reason);
    }

    public function test_publish_sets_published_at(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->approved()->create();
        $this->post(route('admin.content.publish', $content));

        $content->refresh();
        $this->assertNotNull($content->published_at);
    }

    public function test_approve_clears_rejection_reason(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $content = ContentItem::factory()->rejected()->create();
        $this->post(route('admin.content.re-review', $content));
        $this->post(route('admin.content.approve', $content));

        $content->refresh();
        $this->assertNull($content->rejection_reason);
    }

    // ── Model method tests ────────────────────────────────────

    public function test_assign_age_bands_replaces_existing(): void
    {
        $content = ContentItem::factory()->create();
        $content->assignAgeBands([AgeBand::Teen->value]);
        $this->assertEquals([AgeBand::Teen->value], $content->ageBandValues()->all());

        $content->assignAgeBands([AgeBand::Adult->value, AgeBand::AllAges->value]);
        $this->assertEqualsCanonicalizing(
            [AgeBand::Adult->value, AgeBand::AllAges->value],
            $content->ageBandValues()->all()
        );
    }

    public function test_has_age_bands_returns_false_when_empty(): void
    {
        $content = ContentItem::factory()->create();
        $this->assertFalse($content->hasAgeBands());
    }

    public function test_has_age_bands_returns_true_when_assigned(): void
    {
        $content = ContentItem::factory()->create();
        $content->assignAgeBands([AgeBand::AllAges->value]);
        $this->assertTrue($content->hasAgeBands());
    }

    public function test_assign_categories_syncs(): void
    {
        $content = ContentItem::factory()->create();
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        $content->assignCategories([$cat1->id]);
        $this->assertEquals([$cat1->id], $content->categories()->pluck('categories.id')->all());

        $content->assignCategories([$cat2->id]);
        $this->assertEquals([$cat2->id], $content->categories()->pluck('categories.id')->all());
    }
}
