<?php

namespace App\Repositories\Sales\Orders;

use App\Contracts\Sales\Orders\OrderProductResolverContract;
use App\Data\Sales\Orders\ResolvedOrderProduct;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Validation\ValidationException;

final class EloquentOrderProductResolver implements OrderProductResolverContract
{
    public function resolve(
        string $orderType,
        int $productId,
        ?int $variantId
    ): ResolvedOrderProduct {
        $product = Product::query()
            ->visibleFor($orderType)
            ->findOrFail($productId);

        if ($variantId) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->find($variantId);

            if (! $variant) {
                throw ValidationException::withMessages([
                    'items' => 'Selected product variant is invalid or inactive.',
                ]);
            }

            return new ResolvedOrderProduct($product, $variant);
        }

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return new ResolvedOrderProduct($product, $variant);
    }
}
