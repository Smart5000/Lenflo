<?php

namespace App\Actions\Admin;

use App\Enums\ModerationStatus;
use App\Models\Content\ContentItem;
use Illuminate\Database\UniqueConstraintViolationException;

class ImportYouTubeVideo
{
    /**
     * Import a YouTube video as a Candidate content item.
     *
     * @param  array{id: string, title: string, description: string, thumbnail: string, channel: string, durationSeconds: int|null, publishedAt: string, language: string|null}  $videoData
     */
    public function execute(array $videoData): ContentItem
    {
        try {
            return ContentItem::create([
                'provider' => 'youtube',
                'external_id' => $videoData['id'],
                'title' => $videoData['title'],
                'description' => $videoData['description'] ?: null,
                'thumbnail_url' => $videoData['thumbnail'] ?: null,
                'embed_url' => "https://www.youtube.com/embed/{$videoData['id']}",
                'duration_seconds' => $videoData['durationSeconds'] ?? null,
                'original_language_code' => $videoData['language'] ?? null,
                'moderation_status' => ModerationStatus::Candidate->value,
            ]);
        } catch (UniqueConstraintViolationException) {
            return ContentItem::where('provider', 'youtube')
                ->where('external_id', $videoData['id'])
                ->firstOrFail();
        }
    }
}
