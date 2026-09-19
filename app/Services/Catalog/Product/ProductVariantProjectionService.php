<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductVariantProjectionContract;
use App\Models\Catalog\ProductVariant;

final class ProductVariantProjectionService implements ProductVariantProjectionContract
{
    public function fromRows(array $variants): array
    {
        $rows = array_values(array_filter($variants, fn (mixed $row): bool => is_array($row)));
        $main = collect($rows)->first(
            fn (array $row): bool => $this->enabled($row['is_active'] ?? true)
                && $this->enabled($row['is_default'] ?? false)
        );
        $main ??= collect($rows)->first(fn (array $row): bool => $this->enabled($row['is_active'] ?? true));
        $main ??= $rows[0] ?? [];

        return $this->project($main);
    }

    public function fromVariant(ProductVariant $variant): array
    {
        return $this->project($variant->getAttributes());
    }

    private function project(array $variant): array
    {
        return [
            'unit_id' => filled($variant['unit_id'] ?? null) ? (int) $variant['unit_id'] : null,
            'sku' => trim((string) ($variant['variant_sku'] ?? '')),
            'hsn_code' => filled($variant['hsn_code'] ?? null) ? trim((string) $variant['hsn_code']) : null,
            'gst_percent' => (float) ($variant['gst_percent'] ?? 0),
            'mrp' => (float) ($variant['mrp'] ?? 0),
            'dealer_price' => (float) ($variant['dealer_price'] ?? 0),
            'customer_price' => (float) ($variant['customer_price'] ?? 0),
        ];
    }

    private function enabled(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
