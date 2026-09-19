<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\OrderPricingContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

final class OrderPricingService implements OrderPricingContract
{
    public function calculate(
        string $orderType,
        Product $product,
        ?ProductVariant $variant,
        float $quantity
    ): array {
        /*
         * Pricing responsibility belongs here.
         *
         * Do not call ProductVariant::priceFor() because that method
         * may lazy-load the product relationship for fallback pricing.
         * This service already receives Product, so no extra DB query
         * or hidden model side effect is required.
         */
        $productPrice = $orderType === 'dealer'
            ? $product->dealer_price
            : $product->customer_price;

        $variantPrice = $variant
            ? (
                $orderType === 'dealer'
                    ? $variant->dealer_price
                    : $variant->customer_price
            )
            : null;

        $unitPrice = (float) (
            $variantPrice
            ?? $productPrice
            ?? 0
        );

        $priceTotal = round($quantity * $unitPrice, 2);
        $gstPercent = (float) $product->gst_percent;

        // Preserve existing variant pricing behavior: variant rate is GST-inclusive.
        if ($variant) {
            $lineTotal = $priceTotal;
            $lineBase = $gstPercent > 0
                ? round($lineTotal / (1 + ($gstPercent / 100)), 2)
                : $lineTotal;
            $gstAmount = round($lineTotal - $lineBase, 2);
        } else {
            // Preserve existing legacy product pricing behavior: GST is added.
            $lineBase = $priceTotal;
            $gstAmount = round($lineBase * ($gstPercent / 100), 2);
            $lineTotal = round($lineBase + $gstAmount, 2);
        }

        return [
            'unit_price' => $unitPrice,
            'gst_percent' => $product->gst_percent,
            'gst_amount' => $gstAmount,
            'line_total' => $lineTotal,
            'line_base' => $lineBase,
        ];
    }
}
