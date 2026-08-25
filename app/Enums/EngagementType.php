<?php

namespace App\Enums;

enum EngagementType: string
{
    case Impression = 'impression';
    case PlayStart = 'play_start';
    case WatchMilestone = 'watch_milestone';
    case Completion = 'completion';
    case Skip = 'skip';
    case Like = 'like';
    case Save = 'save';
    case Share = 'share';
}
