<?php

namespace App\Models\Content;

use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\EngagementEvent;
use App\Models\Like;
use App\Models\Save;
use App\Models\User;
use Database\Factories\Content\ContentItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ContentItem extends Model
{
    /** @use HasFactory<ContentItemFactory> */
    use HasFactory;

    protected $fillable = [
        'provider',
        'external_id',
        'title',
        'description',
        'thumbnail_url',
        'embed_url',
        'duration_seconds',
        'original_language_code',
        'country_code',
        'moderation_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'internal_notes',
        'editorial_score',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'reviewed_at' => 'datetime',
            'editorial_score' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'content_categories')->withTimestamps();
    }

    public function ageBandValues(): Collection
    {
        return \DB::table('content_age_bands')
            ->where('content_item_id', $this->id)
            ->pluck('age_band');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(Save::class);
    }

    public function engagementEvents(): HasMany
    {
        return $this->hasMany(EngagementEvent::class);
    }

    // ── Age band assignment ───────────────────────────────────

    /**
     * Replace all age bands for this content item.
     *
     * @param  array<int, string>  $bands  Array of AgeBand string values.
     */
    public function assignAgeBands(array $bands): void
    {
        \DB::table('content_age_bands')
            ->where('content_item_id', $this->id)
            ->delete();

        $now = now()->toDateTimeString();
        $rows = array_map(fn (string $band) => [
            'content_item_id' => $this->id,
            'age_band' => $band,
            'created_at' => $now,
            'updated_at' => $now,
        ], $bands);

        if ($rows !== []) {
            \DB::table('content_age_bands')->insert($rows);
        }
    }

    public function hasAgeBands(): bool
    {
        return \DB::table('content_age_bands')
            ->where('content_item_id', $this->id)
            ->exists();
    }

    // ── Category assignment ───────────────────────────────────

    /**
     * Replace all categories for this content item.
     *
     * @param  array<int, int>  $categoryIds
     */
    public function assignCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }

    // ── Moderation state transitions ──────────────────────────

    /**
     * Approve this content item.
     */
    public function approve(User $reviewer): void
    {
        $this->update([
            'moderation_status' => ModerationStatus::Approved->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject this content item with an optional reason.
     */
    public function reject(User $reviewer, ?string $reason = null): void
    {
        $this->update([
            'moderation_status' => ModerationStatus::Rejected->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Publish this approved content item.
     */
    public function publish(): void
    {
        $this->update([
            'moderation_status' => ModerationStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    /**
     * Disable this content item (take down from feed).
     */
    public function disable(): void
    {
        $this->update([
            'moderation_status' => ModerationStatus::Disabled->value,
        ]);
    }

    /**
     * Reset rejected or disabled content back to candidate for re-review.
     */
    public function reReview(): void
    {
        $this->update([
            'moderation_status' => ModerationStatus::Candidate->value,
            'rejection_reason' => null,
        ]);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeFeedEligible(Builder $query): Builder
    {
        return $query->whereIn('moderation_status', [
            ModerationStatus::Approved->value,
            ModerationStatus::Published->value,
        ])->whereExists(function ($sub) {
            $sub->select(\DB::raw(1))
                ->from('content_age_bands')
                ->whereColumn('content_age_bands.content_item_id', 'content_items.id');
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('moderation_status', ModerationStatus::PendingReview->value);
    }

    public function scopeCandidate(Builder $query): Builder
    {
        return $query->where('moderation_status', ModerationStatus::Candidate->value);
    }

    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', $countryCode)->orWhereNull('country_code');
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Feed-eligible means: approved or published, AND has at least one age band.
     */
    public function isFeedEligible(): bool
    {
        $statusOk = in_array($this->moderation_status, [
            ModerationStatus::Approved->value,
            ModerationStatus::Published->value,
        ]);

        return $statusOk && $this->hasAgeBands();
    }
}
