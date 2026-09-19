<?php

namespace App\Models\Inventory;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    protected $fillable = ['warehouse_id', 'product_id', 'product_variant_id', 'batch_no', 'manufacturing_date', 'expiry_date', 'purchase_price', 'quantity', 'reserved_quantity', 'low_stock_alert'];

    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            'purchase_price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'reserved_quantity' => 'decimal:3',
            'low_stock_alert' => 'decimal:3',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
