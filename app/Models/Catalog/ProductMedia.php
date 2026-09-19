<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductMedia extends Model
{
    protected $table = 'product_media';

    protected $fillable = [
        'product_id', 'source_type', 'file_path', 'youtube_url', 'title',
        'thumbnail_path', 'language', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->source_type === 'youtube') {
            return $this->youtube_url;
        }

        return filled($this->file_path)
            ? (Str::startsWith($this->file_path, ['http://', 'https://']) ? $this->file_path : asset($this->file_path))
            : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (filled($this->thumbnail_path)) {
            return Str::startsWith($this->thumbnail_path, ['http://', 'https://'])
                ? $this->thumbnail_path
                : asset($this->thumbnail_path);
        }

        if ($this->source_type !== 'youtube' || blank($this->youtube_url)) {
            return null;
        }

        $videoId = $this->youtubeVideoId();

        return $videoId ? 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg' : null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $videoId = $this->youtubeVideoId();

        return $videoId ? 'https://www.youtube.com/embed/'.$videoId : null;
    }

    private function youtubeVideoId(): ?string
    {
        $url = (string) $this->youtube_url;

        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
