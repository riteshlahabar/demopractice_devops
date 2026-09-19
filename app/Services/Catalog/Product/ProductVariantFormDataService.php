<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductVariantFormDataContract;
use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Support\Collection;

final class ProductVariantFormDataService implements ProductVariantFormDataContract
{
    public function __construct(private readonly ProductVariantUnitContract $units) {}

    public function rowsFor(Product $product): array
    {
        $variants = $product->relationLoaded('variants')
            ? $product->variants
            : $product->variants()->with('inventoryBatches')->get();

        $active = $variants->where('is_active', true);

        // Fall back to the inactive rows rather than showing an empty repeater,
        // otherwise reopening such a product hides the prices it already has.
        $rows = $this->rows($active->isNotEmpty() ? $active : $variants, $product);

        return $rows !== [] ? $rows : $this->rowFromProduct($product);
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     * @return array<int, array<string, mixed>>
     */
    private function rows(Collection $variants, Product $product): array
    {
        return $variants->map(function (ProductVariant $variant) use ($product): array {
            preg_match('/^([0-9.]+)\s*([A-Za-z]+)?/', (string) $variant->value, $legacySize);
            $sizeUnit = $variant->size_unit ?: strtoupper((string) ($legacySize[2] ?? ''));

            return [
                'id' => $variant->id,
                'unit_id' => $variant->unit_id ?? $this->units->idForShortName($sizeUnit),
                'size_value' => $variant->size_value ?: ($legacySize[1] ?? null),
                'size_unit' => $sizeUnit,
                'variant_sku' => $variant->variant_sku,
                // Products created before HSN/GST moved into the variant keep
                // those values at product level, so fall back to them and the
                // next save carries them onto the variant.
                'hsn_code' => $variant->hsn_code ?: $product->hsn_code,
                'gst_percent' => $variant->gst_percent ?? $product->gst_percent,
                'units_per_case' => max(1, (int) $variant->units_per_case),
                'mrp' => $variant->mrp ?? $product->mrp,
                'dealer_price' => $variant->dealer_price ?? $product->dealer_price,
                'customer_price' => $variant->customer_price ?? $product->customer_price,
                'sort_order' => $variant->sort_order,
                'is_default' => $variant->is_default,
                'is_active' => $variant->is_active,
                'purchase_price' => 0,
                'low_stock_alert' => 0,
            ];
        })->values()->all();
    }

    /**
     * Products added before packing variants existed keep their price, unit,
     * SKU, HSN and GST on the product row itself. Seeding the first variant
     * with those values means opening such a product for edit shows them
     * instead of an empty row. Pack size is deliberately left blank so the
     * admin enters the real size rather than inheriting a guess.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rowFromProduct(Product $product): array
    {
        if (blank($product->getKey())) {
            return [];
        }

        return [[
            'id' => null,
            'unit_id' => $product->unit_id,
            'size_value' => null,
            'size_unit' => $this->units->shortNameFor($product->unit_id),
            'variant_sku' => $product->sku,
            'hsn_code' => $product->hsn_code,
            'gst_percent' => $product->gst_percent,
            'units_per_case' => 1,
            'mrp' => $product->mrp,
            'dealer_price' => $product->dealer_price,
            'customer_price' => $product->customer_price,
            'sort_order' => 0,
            'is_default' => true,
            'is_active' => true,
            'purchase_price' => 0,
            'low_stock_alert' => 0,
        ]];
    }
}
