<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\OrderLineQuantityContract;
use App\Models\Catalog\ProductVariant;
use Illuminate\Validation\ValidationException;

final class OrderLineQuantityService implements OrderLineQuantityContract
{
    public function normalize(
        string $orderType,
        array $item,
        ?ProductVariant $variant
    ): array {
        $submittedQuantity = round(
            (float) ($item['quantity'] ?? 0),
            3
        );

        if ($submittedQuantity <= 0) {
            throw ValidationException::withMessages([
                'items' => 'Quantity must be greater than zero.',
            ]);
        }

        $unitsPerCase = $variant
            ? max(1.0, (float) $variant->units_per_case)
            : 1.0;

        // Storefront has already normalized dealer case quantity to retail packs.
        if (array_key_exists('pack_quantity', $item)) {
            $displayQuantity = round(
                (float) $item['pack_quantity'],
                3
            );

            if ($displayQuantity <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Quantity must be greater than zero.',
                ]);
            }

            return [
                'quantity' => $submittedQuantity,
                'pack_quantity' => $displayQuantity,
                'units_per_case' => $unitsPerCase,
            ];
        }

        $displayQuantity = $submittedQuantity;
        $stockQuantity = $orderType === 'dealer' && $variant
            ? round($displayQuantity * $unitsPerCase, 3)
            : $displayQuantity;

        return [
            'quantity' => $stockQuantity,
            'pack_quantity' => $displayQuantity,
            'units_per_case' => $unitsPerCase,
        ];
    }
}
