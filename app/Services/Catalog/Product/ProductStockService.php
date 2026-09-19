<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductStockContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryBatch;

final class ProductStockService implements ProductStockContract
{
    public function createOpeningStock(Product $product, ?array $stock): void
    {
        if (! $stock || blank($stock['warehouse_id'] ?? null) || blank($stock['batch_no'] ?? null) || ($stock['quantity'] ?? null) === null) {
            return;
        }

        InventoryBatch::query()->create(array_merge($stock, ['product_id' => $product->getKey()]));
    }

    public function syncVariantOpeningStock(Product $product, ProductVariant $variant, array $row): void
    {
        if (blank($row['warehouse_id'] ?? null) || blank($row['batch_no'] ?? null) || ($row['opening_stock_quantity'] ?? null) === null || $row['opening_stock_quantity'] === '') {
            return;
        }

        $batch = InventoryBatch::query()->firstOrNew([
            'warehouse_id' => (int) $row['warehouse_id'],
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'batch_no' => trim((string) $row['batch_no']),
        ]);
        $batch->fill([
            'manufacturing_date' => $row['manufacturing_date'] ?? null,
            'expiry_date' => $row['expiry_date'] ?? null,
            'purchase_price' => (float) ($row['purchase_price'] ?? 0),
            'quantity' => (float) $row['opening_stock_quantity'],
            'low_stock_alert' => (float) ($row['low_stock_alert'] ?? 0),
        ]);
        if (! $batch->exists) {
            $batch->reserved_quantity = 0;
        }
        $batch->save();

        $variant->forceFill(['stock_quantity' => $variant->inventoryBatches()->sum('quantity')])->save();
    }
}
