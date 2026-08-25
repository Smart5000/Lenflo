<?php

namespace App\Enums;

enum ModerationStatus: string
{
    case Candidate = 'candidate';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Candidate => 'Candidate',
            self::PendingReview => 'Pending Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Published => 'Published',
            self::Disabled => 'Disabled',
        };
    }

    public function isFeedEligible(): bool
    {
        return $this === self::Approved || $this === self::Published;
    }
}
