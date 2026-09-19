<?php

namespace App\Models\Catalog;

use App\Models\Inventory\InventoryBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'unit_id', 'group_name', 'value', 'size_value', 'size_unit',
        'variant_sku', 'hsn_code', 'gst_percent',
        'units_per_case', 'mrp', 'dealer_price', 'customer_price', 'price_difference',
        'stock_quantity', 'is_default', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gst_percent' => 'decimal:2',
            'price_difference' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
            'size_value' => 'decimal:3',
            'units_per_case' => 'decimal:3',
            'mrp' => 'decimal:2',
            'dealer_price' => 'decimal:2',
            'customer_price' => 'decimal:2',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'product_variant_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if (filled($this->size_value) && filled($this->size_unit)) {
            return rtrim(rtrim(number_format((float) $this->size_value, 3, '.', ''), '0'), '.').' '.strtoupper((string) $this->size_unit);
        }

        return (string) $this->value;
    }

    public function getAvailableStockAttribute(): float
    {
        if ($this->relationLoaded('inventoryBatches') && $this->inventoryBatches->isNotEmpty()) {
            return (float) $this->inventoryBatches
                ->filter(fn (InventoryBatch $batch): bool => ! $batch->expiry_date || $batch->expiry_date->endOfDay()->isFuture())
                ->sum(fn (InventoryBatch $batch): float => max(0, (float) $batch->quantity - (float) $batch->reserved_quantity));
        }

        return (float) ($this->stock_quantity ?? 0);
    }

    public function priceFor(string $audience): float
    {
        $variantPrice = $audience === 'dealer' ? $this->dealer_price : $this->customer_price;
        $productPrice = $audience === 'dealer' ? $this->product?->dealer_price : $this->product?->customer_price;

        return (float) ($variantPrice ?? $productPrice ?? 0);
    }
}
