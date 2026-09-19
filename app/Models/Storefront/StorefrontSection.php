<?php

namespace App\Models\Storefront;

use App\Models\Catalog\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorefrontSection extends Model
{
    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
        'image_path',
        'section_type',
        'source_type',
        'category_id',
        'product_limit',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sectionProducts(): HasMany
    {
        return $this->hasMany(StorefrontSectionProduct::class, 'section_id');
    }
}
