<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class YouTubeService
{
    private string $apiKey;

    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key') ?? '';
    }

    /**
     * Search YouTube for videos.
     *
     * @return array{items: array<int, array<string, mixed>>, nextPageToken: string|null}
     */
    public function search(string $query, int $maxResults = 12, ?string $pageToken = null): array
    {
        $this->ensureApiKey();

        $params = [
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'videoEmbeddable' => 'true',
            'maxResults' => $maxResults,
            'key' => $this->apiKey,
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = Http::timeout(10)
            ->get("{$this->baseUrl}/search", $params)
            ->throw();

        $data = $response->json();

        return [
            'items' => array_map([$this, 'normalizeSearchResult'], $data['items'] ?? []),
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    /**
     * Get detailed video information.
     *
     * @return array{id: string, title: string, description: string, thumbnail: string, channel: string, durationSeconds: int|null, publishedAt: string, language: string|null}|null
     */
    public function getVideoDetails(string $videoId): ?array
    {
        $this->ensureApiKey();

        $response = Http::timeout(10)
            ->get("{$this->baseUrl}/videos", [
                'part' => 'snippet,contentDetails',
                'id' => $videoId,
                'key' => $this->apiKey,
            ])
            ->throw();

        $data = $response->json();
        $items = $data['items'] ?? [];

        if (empty($items)) {
            return null;
        }

        return $this->normalizeVideoDetails($items[0]);
    }

    /**
     * Extract a YouTube video ID from various URL formats.
     */
    public function extractVideoId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?.*v=|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Check if the YouTube API key is configured.
     */
    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    private function ensureApiKey(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('YouTube API key is not configured. Set YOUTUBE_API_KEY in your .env file.');
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{id: string, title: string, thumbnail: string, channel: string, publishedAt: string}
     */
    private function normalizeSearchResult(array $item): array
    {
        $snippet = $item['snippet'] ?? [];
        $thumbnails = $snippet['thumbnails'] ?? [];

        return [
            'id' => $item['id']['videoId'] ?? '',
            'title' => $snippet['title'] ?? '',
            'thumbnail' => $thumbnails['medium']['url'] ?? $thumbnails['default']['url'] ?? '',
            'channel' => $snippet['channelTitle'] ?? '',
            'publishedAt' => $snippet['publishedAt'] ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{id: string, title: string, description: string, thumbnail: string, channel: string, durationSeconds: int|null, publishedAt: string, language: string|null}
     */
    private function normalizeVideoDetails(array $item): array
    {
        $snippet = $item['snippet'] ?? [];
        $contentDetails = $item['contentDetails'] ?? [];
        $thumbnails = $snippet['thumbnails'] ?? [];

        return [
            'id' => $item['id'] ?? '',
            'title' => $snippet['title'] ?? '',
            'description' => $snippet['description'] ?? '',
            'thumbnail' => $thumbnails['high']['url'] ?? $thumbnails['medium']['url'] ?? $thumbnails['default']['url'] ?? '',
            'channel' => $snippet['channelTitle'] ?? '',
            'durationSeconds' => $this->parseDuration($contentDetails['duration'] ?? null),
            'publishedAt' => $snippet['publishedAt'] ?? '',
            'language' => $snippet['defaultLanguage'] ?? null,
        ];
    }

    /**
     * Parse ISO 8601 duration (e.g., PT1H2M3S) to seconds.
     */
    private function parseDuration(?string $duration): ?int
    {
        if ($duration === null) {
            return null;
        }

        if (! preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $duration, $matches)) {
            return null;
        }

        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);
        $seconds = (int) ($matches[3] ?? 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}
