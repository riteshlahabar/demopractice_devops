<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\StorefrontSessionProductRulesContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Validation\ValidationException;

final class StorefrontSessionProductRules implements StorefrontSessionProductRulesContract
{
    public function assertVisible(Product $product, string $audience): void
    {
        $column = $audience === 'dealer' ? 'is_visible_to_dealers' : 'is_visible_to_customers';

        if (! $product->is_active || ! (bool) $product->{$column}) {
            throw ValidationException::withMessages([
                'product' => 'This product is not available for the selected account.',
            ]);
        }
    }

    public function variantForEntry(Product $product, array $entry): ?ProductVariant
    {
        $variantId = (int) ($entry['variant_id'] ?? 0);
        if ($variantId <= 0) {
            return null;
        }

        return $product->variants->firstWhere('id', $variantId);
    }

    public function unitQuantity(string $audience, float $quantity, ?ProductVariant $variant): float
    {
        if ($audience !== 'dealer' || ! $variant) {
            return round($quantity, 3);
        }

        return round($quantity * max(1, (float) $variant->units_per_case), 3);
    }

    public function availableStock(Product $product, ?ProductVariant $variant): float
    {
        return $variant ? (float) $variant->available_stock : (float) $product->available_stock;
    }

    public function unitPrice(Product $product, string $audience, ?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $variant->priceFor($audience);
        }

        return (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    }

    public function lineKey(int $productId, ?int $variantId): string
    {
        return $productId.':'.($variantId ?: 0);
    }
}
