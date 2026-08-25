<?php

namespace App\Enums;

enum AgeBand: string
{
    case EarlyLearner = 'early_learner';
    case YoungExplorer = 'young_explorer';
    case CuriousMind = 'curious_mind';
    case Teen = 'teen';
    case YoungAdult = 'young_adult';
    case Adult = 'adult';
    case AllAges = 'all_ages';

    public function label(): string
    {
        return match ($this) {
            self::EarlyLearner => 'Early Learner (3-5)',
            self::YoungExplorer => 'Young Explorer (6-8)',
            self::CuriousMind => 'Curious Mind (9-12)',
            self::Teen => 'Teen (13-15)',
            self::YoungAdult => 'Young Adult (16-17)',
            self::Adult => 'Adult (18+)',
            self::AllAges => 'All Ages',
        };
    }

    public function minAge(): int
    {
        return match ($this) {
            self::EarlyLearner => 3,
            self::YoungExplorer => 6,
            self::CuriousMind => 9,
            self::Teen => 13,
            self::YoungAdult => 16,
            self::Adult => 18,
            self::AllAges => 0,
        };
    }

    public function maxAge(): ?int
    {
        return match ($this) {
            self::EarlyLearner => 5,
            self::YoungExplorer => 8,
            self::CuriousMind => 12,
            self::Teen => 15,
            self::YoungAdult => 17,
            self::Adult, self::AllAges => null,
        };
    }
}
