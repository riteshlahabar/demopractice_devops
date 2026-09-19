<?php

namespace App\Models\Catalog;

use App\Support\ImageAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'is_primary', 'sort_order'];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return ImageAsset::url($this->path);
    }
}
