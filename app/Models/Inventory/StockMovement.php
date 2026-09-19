<?php

namespace App\Models\Inventory;

use App\Models\Catalog\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['inventory_batch_id', 'product_variant_id', 'movement_type', 'quantity', 'reference_type', 'reference_id', 'created_by'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
