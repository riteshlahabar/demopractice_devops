<?php

namespace App\Models\Storefront;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontSectionProduct extends Model
{
    protected $fillable = ['section_id', 'product_id', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(StorefrontSection::class, 'section_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
