<?php

namespace App\Http\Controllers\Feed;

use App\Enums\EngagementType;
use App\Http\Controllers\Controller;
use App\Models\Content\ContentItem;
use App\Models\EngagementEvent;
use App\Models\Like;
use App\Models\Save;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LikeSaveController extends Controller
{
    public function toggleLike(Request $request, ContentItem $contentItem): JsonResponse
    {
        Gate::authorize('like', $contentItem);

        $user = $request->user();

        $existing = Like::where('user_id', $user->id)
            ->where('content_item_id', $contentItem->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'content_item_id' => $contentItem->id,
            ]);
            $liked = true;

            EngagementEvent::create([
                'user_id' => $user->id,
                'content_item_id' => $contentItem->id,
                'type' => EngagementType::Like,
            ]);
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $contentItem->likes()->count(),
        ]);
    }

    public function toggleSave(Request $request, ContentItem $contentItem): JsonResponse
    {
        Gate::authorize('save', $contentItem);

        $user = $request->user();

        $existing = Save::where('user_id', $user->id)
            ->where('content_item_id', $contentItem->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            Save::create([
                'user_id' => $user->id,
                'content_item_id' => $contentItem->id,
            ]);
            $saved = true;

            EngagementEvent::create([
                'user_id' => $user->id,
                'content_item_id' => $contentItem->id,
                'type' => EngagementType::Save,
            ]);
        }

        return response()->json([
            'saved' => $saved,
            'saves_count' => $contentItem->saves()->count(),
        ]);
    }

    public function share(Request $request, ContentItem $contentItem): JsonResponse
    {
        Gate::authorize('share', $contentItem);

        EngagementEvent::create([
            'user_id' => $request->user()->id,
            'content_item_id' => $contentItem->id,
            'type' => EngagementType::Share,
        ]);

        return response()->json(['shared' => true]);
    }
}
