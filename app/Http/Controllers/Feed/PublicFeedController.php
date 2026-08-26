<?php

namespace App\Http\Controllers\Feed;

use App\Enums\AgeBand;
use App\Http\Controllers\Controller;
use App\Models\Content\ContentItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicFeedController extends Controller
{
    /**
     * Display the public learning feed.
     */
    public function index(Request $request): Response
    {
        $query = ContentItem::feedEligible()
            ->with(['categories'])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        $user = $request->user();

        if ($user && $user->birth_date) {
            $age = (int) $user->birth_date->age;
            $matchingBands = collect(AgeBand::cases())
                ->filter(fn (AgeBand $band) => $age >= $band->minAge() && ($band->maxAge() === null || $age <= $band->maxAge()))
                ->pluck('value')
                ->all();

            $query->where(function ($q) use ($matchingBands) {
                $q->whereIn('id', function ($sub) use ($matchingBands) {
                    $sub->select('content_item_id')
                        ->from('content_age_bands')
                        ->whereIn('age_band', $matchingBands);
                })->orWhereIn('id', function ($sub) {
                    $sub->select('content_item_id')
                        ->from('content_age_bands')
                        ->where('age_band', AgeBand::AllAges->value);
                });
            });
        }

        $contentItems = $query->limit(30)->get();

        $userLikes = [];
        $userSaves = [];

        if ($user) {
            $likedIds = $user->likes()->pluck('content_item_id')->all();
            $savedIds = $user->saves()->pluck('content_item_id')->all();
            $userLikes = array_fill_keys($likedIds, true);
            $userSaves = array_fill_keys($savedIds, true);
        }

        return Inertia::render('feed/index', [
            'contentItems' => $contentItems->map(fn (ContentItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'provider' => $item->provider,
                'external_id' => $item->external_id,
                'embed_url' => $item->embed_url,
                'thumbnail_url' => $item->thumbnail_url,
                'duration_seconds' => $item->duration_seconds,
                'categories' => $item->categories,
                'likes_count' => $item->likes()->count(),
                'saves_count' => $item->saves()->count(),
            ]),
            'userLikes' => $userLikes,
            'userSaves' => $userSaves,
            'meta' => [
                'title' => 'Feed',
                'description' => 'Discover age-relevant learning content.',
            ],
        ]);
    }
}
