<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionProductRepositoryContract;
use App\Contracts\Storefront\Session\StorefrontCartStorageContract;
use App\Contracts\Storefront\Session\StorefrontCartSummaryContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontSessionProductRulesContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class StorefrontCartSummaryService implements StorefrontCartSummaryContract
{
    public function __construct(
        private readonly StorefrontCartStorageContract $cart,
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontSessionProductRepositoryContract $products,
        private readonly StorefrontSessionProductRulesContract $rules
    ) {}

    public function summary(Request $request): array
    {
        $cart = $this->cart->cart($request);
        $products = $this->productsForCart($request, $cart);
        $audience = $this->identity->audience($request);
        $subtotal = 0.0;
        $gstTotal = 0.0;
        $count = 0.0;
        $hasIssues = false;
        $items = collect();

        foreach ($cart as $lineKey => $entry) {
            $product = $products->get((int) $entry['product_id']);
            if (! $product) {
                $hasIssues = true;

                continue;
            }

            $variant = $this->rules->variantForEntry($product, $entry);
            if (! empty($entry['variant_id']) && ! $variant) {
                $hasIssues = true;

                continue;
            }

            $quantity = (float) $entry['quantity'];
            $unitsPerCase = $variant ? max(1, (float) $variant->units_per_case) : 1.0;
            $unitQuantity = $audience === 'dealer'
                ? round($quantity * $unitsPerCase, 3)
                : $quantity;
            $unitPrice = $this->rules->unitPrice($product, $audience, $variant);
            $priceTotal = round($unitPrice * $unitQuantity, 2);
            $gstPercent = (float) $product->gst_percent;

            if ($variant) {
                $lineTotal = $priceTotal;
                $lineBase = $gstPercent > 0
                    ? round($lineTotal / (1 + ($gstPercent / 100)), 2)
                    : $lineTotal;
                $gstAmount = round($lineTotal - $lineBase, 2);
            } else {
                $lineBase = $priceTotal;
                $gstAmount = round($lineBase * ($gstPercent / 100), 2);
                $lineTotal = round($lineBase + $gstAmount, 2);
            }

            $availableStock = $this->rules->availableStock($product, $variant);
            $itemHasIssue = $availableStock + 0.0001 < $unitQuantity;

            $items->push([
                'line_key' => (string) $lineKey,
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'unit_quantity' => $unitQuantity,
                'units_per_case' => $unitsPerCase,
                'unit_price' => $unitPrice,
                'line_base' => $lineBase,
                'gst_amount' => $gstAmount,
                'line_total' => $lineTotal,
                'available_stock' => $availableStock,
                'has_issue' => $itemHasIssue,
                'quantity_label' => $audience === 'dealer' && $variant
                    ? 'case(s)'
                    : 'retail pack(s)',
            ]);

            $subtotal += $lineBase;
            $gstTotal += $gstAmount;
            $count += $quantity;
            $hasIssues = $hasIssues || $itemHasIssue;
        }

        return [
            'items' => $items,
            'count' => $count,
            'subtotal' => round($subtotal, 2),
            'gst_total' => round($gstTotal, 2),
            'grand_total' => round($subtotal + $gstTotal, 2),
            'has_issues' => $hasIssues,
        ];
    }

    public function checkoutItems(Request $request): array
    {
        return collect($this->summary($request)['items'])
            ->map(fn (array $item): array => [
                'product_id' => $item['product']->id,
                'variant_id' => $item['variant']?->id,
                'quantity' => $item['unit_quantity'],
                'pack_quantity' => $item['quantity'],
                'units_per_case' => $item['units_per_case'],
            ])
            ->values()
            ->all();
    }

    private function productsForCart(Request $request, array $cart): Collection
    {
        $productIds = collect($cart)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return $this->products->visibleByIds(
            $this->identity->audience($request),
            $productIds->all()
        );
    }
}
