<?php

namespace App\Models;

use App\Enums\EngagementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_item_id',
        'type',
        'watch_seconds',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => EngagementType::class,
            'watch_seconds' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(Content\ContentItem::class);
    }
}
