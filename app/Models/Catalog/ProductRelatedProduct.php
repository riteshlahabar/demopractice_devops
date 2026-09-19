<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRelatedProduct extends Model
{
    protected $fillable = ['product_id', 'related_product_id', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function relatedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}
