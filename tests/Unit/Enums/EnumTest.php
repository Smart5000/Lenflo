<?php

namespace Tests\Unit\Enums;

use App\Enums\AgeBand;
use App\Enums\EngagementType;
use App\Enums\ModerationStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnumTest extends TestCase
{
    #[Test]
    public function moderation_status_has_required_values(): void
    {
        $values = array_column(ModerationStatus::cases(), 'value');

        $this->assertContains('candidate', $values);
        $this->assertContains('pending_review', $values);
        $this->assertContains('approved', $values);
        $this->assertContains('rejected', $values);
        $this->assertContains('published', $values);
        $this->assertContains('disabled', $values);
    }

    #[Test]
    public function moderation_status_feed_eligibility(): void
    {
        $this->assertTrue(ModerationStatus::Approved->isFeedEligible());
        $this->assertTrue(ModerationStatus::Published->isFeedEligible());
        $this->assertFalse(ModerationStatus::Candidate->isFeedEligible());
        $this->assertFalse(ModerationStatus::PendingReview->isFeedEligible());
        $this->assertFalse(ModerationStatus::Rejected->isFeedEligible());
        $this->assertFalse(ModerationStatus::Disabled->isFeedEligible());
    }

    #[Test]
    public function age_band_has_required_values(): void
    {
        $values = array_column(AgeBand::cases(), 'value');

        $this->assertContains('early_learner', $values);
        $this->assertContains('young_explorer', $values);
        $this->assertContains('curious_mind', $values);
        $this->assertContains('teen', $values);
        $this->assertContains('young_adult', $values);
        $this->assertContains('adult', $values);
        $this->assertContains('all_ages', $values);
    }

    #[Test]
    public function age_band_age_ranges_are_valid(): void
    {
        $this->assertSame(3, AgeBand::EarlyLearner->minAge());
        $this->assertSame(5, AgeBand::EarlyLearner->maxAge());
        $this->assertSame(18, AgeBand::Adult->minAge());
        $this->assertNull(AgeBand::Adult->maxAge());
        $this->assertSame(0, AgeBand::AllAges->minAge());
        $this->assertNull(AgeBand::AllAges->maxAge());
    }

    #[Test]
    public function engagement_type_has_required_values(): void
    {
        $values = array_column(EngagementType::cases(), 'value');

        $this->assertContains('impression', $values);
        $this->assertContains('play_start', $values);
        $this->assertContains('watch_milestone', $values);
        $this->assertContains('completion', $values);
        $this->assertContains('skip', $values);
        $this->assertContains('like', $values);
        $this->assertContains('save', $values);
        $this->assertContains('share', $values);
    }
}
