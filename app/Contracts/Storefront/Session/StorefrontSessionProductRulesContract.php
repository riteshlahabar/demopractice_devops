<?php

namespace App\Contracts\Storefront\Session;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

interface StorefrontSessionProductRulesContract
{
    public function assertVisible(Product $product, string $audience): void;

    public function variantForEntry(Product $product, array $entry): ?ProductVariant;

    public function unitQuantity(string $audience, float $quantity, ?ProductVariant $variant): float;

    public function availableStock(Product $product, ?ProductVariant $variant): float;

    public function unitPrice(Product $product, string $audience, ?ProductVariant $variant = null): float;

    public function lineKey(int $productId, ?int $variantId): string;
}
