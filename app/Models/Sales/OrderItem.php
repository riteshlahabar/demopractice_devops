<?php

namespace App\Models\Sales;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'variant_name', 'quantity', 'pack_quantity', 'units_per_case', 'unit_price', 'gst_percent', 'gst_amount', 'line_total'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'pack_quantity' => 'decimal:3', 'units_per_case' => 'decimal:3', 'unit_price' => 'decimal:2', 'gst_percent' => 'decimal:2', 'gst_amount' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
