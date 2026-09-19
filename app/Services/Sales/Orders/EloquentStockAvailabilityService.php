<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\StockAvailabilityContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryBatch;
use Illuminate\Validation\ValidationException;

final class EloquentStockAvailabilityService implements StockAvailabilityContract
{
    public function ensureAvailable(
        Product $product,
        float $requestedQuantity,
        ?ProductVariant $variant = null
    ): void {
        if (! config('orders.enforce_stock', true)) {
            return;
        }

        $available = (float) InventoryBatch::query()
            ->where('product_id', $product->id)
            ->when(
                $variant,
                fn ($query) => $query->where('product_variant_id', $variant->id),
                fn ($query) => $query->whereNull('product_variant_id')
            )
            ->where(function ($query): void {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today());
            })
            ->selectRaw(
                'COALESCE(SUM(quantity - reserved_quantity), 0) as available_quantity'
            )
            ->value('available_quantity');

        if ($available + 0.0001 < $requestedQuantity) {
            throw ValidationException::withMessages([
                'items' => 'Insufficient stock for '
                    .$product->name
                    .'. Available quantity: '
                    .number_format($available, 3),
            ]);
        }
    }
}
