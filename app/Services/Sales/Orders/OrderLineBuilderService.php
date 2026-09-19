<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\OrderLineBuilderContract;
use App\Contracts\Sales\Orders\OrderLineQuantityContract;
use App\Contracts\Sales\Orders\OrderPricingContract;
use App\Contracts\Sales\Orders\OrderProductResolverContract;
use App\Contracts\Sales\Orders\StockAvailabilityContract;

final class OrderLineBuilderService implements OrderLineBuilderContract
{
    public function __construct(
        private readonly OrderProductResolverContract $products,
        private readonly OrderLineQuantityContract $quantities,
        private readonly OrderPricingContract $pricing,
        private readonly StockAvailabilityContract $stock
    ) {}

    public function build(string $orderType, array $items): array
    {
        $requested = [];

        foreach ($items as $item) {
            $resolved = $this->products->resolve(
                $orderType,
                (int) $item['product_id'],
                (int) ($item['variant_id'] ?? 0) ?: null
            );

            $normalized = $this->quantities->normalize(
                $orderType,
                $item,
                $resolved->variant
            );

            $key = $resolved->product->id
                .':'
                .($resolved->variant?->id ?: 0);

            if (! isset($requested[$key])) {
                $requested[$key] = [
                    'product' => $resolved->product,
                    'variant' => $resolved->variant,
                    'quantity' => 0.0,
                    'pack_quantity' => 0.0,
                    'units_per_case' => (float) $normalized['units_per_case'],
                ];
            }

            $requested[$key]['quantity'] +=
                (float) $normalized['quantity'];
            $requested[$key]['pack_quantity'] +=
                (float) $normalized['pack_quantity'];
        }

        $lineItems = [];

        foreach ($requested as $requestLine) {
            $product = $requestLine['product'];
            $variant = $requestLine['variant'];
            $quantity = round((float) $requestLine['quantity'], 3);

            $this->stock->ensureAvailable(
                $product,
                $quantity,
                $variant
            );

            $price = $this->pricing->calculate(
                $orderType,
                $product,
                $variant,
                $quantity
            );

            $lineItems[] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'variant_name' => $variant?->display_name,
                'quantity' => $quantity,
                'pack_quantity' => round(
                    (float) $requestLine['pack_quantity'],
                    3
                ),
                'units_per_case' => (float) $requestLine['units_per_case'],
                ...$price,
            ];
        }

        return $lineItems;
    }
}
