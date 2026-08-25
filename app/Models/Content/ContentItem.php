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

    public function scopeFeedEligible(Builder $query): Builder
    {
        return $query->whereIn('moderation_status', [
            ModerationStatus::Approved->value,
            ModerationStatus::Published->value,
        ]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('moderation_status', ModerationStatus::PendingReview->value);
    }

    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', $countryCode)->orWhereNull('country_code');
    }

    public function isFeedEligible(): bool
    {
        return in_array($this->moderation_status, [
            ModerationStatus::Approved->value,
            ModerationStatus::Published->value,
        ]);
    }
}
