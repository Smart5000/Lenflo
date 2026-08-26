<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ImportYouTubeVideo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportYouTubeUrlRequest;
use App\Models\Content\ContentItem;
use App\Services\YouTubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YouTubeController extends Controller
{
    public function __construct(
        private YouTubeService $youtube,
    ) {}

    /**
     * Show the YouTube search page with optional results.
     */
    public function search(Request $request): Response
    {
        $query = $request->input('q');
        $results = null;
        $nextPageToken = null;
        $error = null;

        if ($query) {
            try {
                $searchResponse = $this->youtube->search($query);
                $results = $searchResponse['items'];
                $nextPageToken = $searchResponse['nextPageToken'];

                $importedIds = ContentItem::where('provider', 'youtube')
                    ->whereIn('external_id', array_column($results, 'id'))
                    ->pluck('external_id')
                    ->all();

                foreach ($results as &$item) {
                    $item['already_imported'] = in_array($item['id'], $importedIds);
                }
                unset($item);
            } catch (\Exception $e) {
                $error = 'Failed to search YouTube. Please check your API key configuration.';
            }
        }

        return Inertia::render('admin/youtube/search', [
            'query' => $query,
            'results' => $results,
            'nextPageToken' => $nextPageToken,
            'error' => $error,
            'apiKeyConfigured' => $this->youtube->isConfigured(),
        ]);
    }

    /**
     * Import a video from YouTube search results.
     */
    public function import(Request $request, ImportYouTubeVideo $importAction): RedirectResponse
    {
        $request->validate([
            'video_id' => ['required', 'string'],
        ]);

        $videoId = $request->input('video_id');
        $videoData = $this->youtube->getVideoDetails($videoId);

        if ($videoData === null) {
            return back()->withErrors(['video_id' => 'Video not found on YouTube.']);
        }

        $contentItem = $importAction->execute($videoData);

        return to_route('admin.content.show', $contentItem)
            ->with('toast', ['type' => 'success', 'message' => "Imported \"{$contentItem->title}\" as a candidate for review."]);
    }

    /**
     * Import a video by pasting a YouTube URL.
     */
    public function importUrl(ImportYouTubeUrlRequest $request, ImportYouTubeVideo $importAction): RedirectResponse
    {
        $videoId = $this->youtube->extractVideoId($request->input('url'));

        $videoData = $this->youtube->getVideoDetails($videoId);

        if ($videoData === null) {
            return back()->withErrors(['url' => 'Video not found on YouTube.']);
        }

        $contentItem = $importAction->execute($videoData);

        return to_route('admin.content.show', $contentItem)
            ->with('toast', ['type' => 'success', 'message' => "Imported \"{$contentItem->title}\" as a candidate for review."]);
    }
}
