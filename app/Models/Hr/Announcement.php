<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'audience', 'category', 'attachment_path',
        'published_at', 'expires_at', 'created_by',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    /**
     * Published, not yet expired, and aimed at this audience (or everyone).
     */
    public function scopeVisibleTo(Builder $query, string $audience): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereIn('audience', [$audience, 'all'])
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
