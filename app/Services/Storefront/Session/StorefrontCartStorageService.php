<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionProductRepositoryContract;
use App\Contracts\Storefront\Session\StorefrontCartStorageContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontSessionProductRulesContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class StorefrontCartStorageService implements StorefrontCartStorageContract
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontSessionProductRepositoryContract $products,
        private readonly StorefrontSessionProductRulesContract $rules
    ) {}

    public function add(
        Request $request,
        Product $product,
        float $quantity,
        ?ProductVariant $variant = null
    ): void {
        $quantity = round($quantity, 3);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        $this->rules->assertVisible($product, $this->identity->audience($request));

        $variant = $variant ?: $product->mainVariant();
        if ($variant && ((int) $variant->product_id !== (int) $product->id || ! $variant->is_active)) {
            throw ValidationException::withMessages([
                'variant_id' => 'The selected size/pack is not available.',
            ]);
        }

        $cart = $this->cart($request);
        $lineKey = $this->rules->lineKey($product->id, $variant?->id);
        $nextQuantity = round(((float) data_get($cart, $lineKey.'.quantity', 0)) + $quantity, 3);
        $unitQuantity = $this->rules->unitQuantity(
            $this->identity->audience($request),
            $nextQuantity,
            $variant
        );
        $availableStock = $this->rules->availableStock($product, $variant);

        if ($unitQuantity > $availableStock + 0.0001) {
            throw ValidationException::withMessages([
                'quantity' => 'Only '.number_format($availableStock, 3)
                    .' retail packs are available for '.$product->translatedName().'.',
            ]);
        }

        $cart[$lineKey] = [
            'product_id' => (int) $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $nextQuantity,
        ];
        $this->store($request, $cart);
    }

    public function update(Request $request, array $items): void
    {
        $currentCart = $this->cart($request);
        $products = $this->productsForCart($request, $currentCart);
        $cart = [];

        foreach ($items as $lineKey => $quantity) {
            $quantity = round((float) $quantity, 3);

            if ($quantity <= 0) {
                continue;
            }

            $entry = $currentCart[(string) $lineKey] ?? null;
            if (! is_array($entry)) {
                continue;
            }

            $product = $products->get((int) $entry['product_id']);
            $variant = $product ? $this->rules->variantForEntry($product, $entry) : null;
            if (! $product || (! empty($entry['variant_id']) && ! $variant)) {
                continue;
            }

            $unitQuantity = $this->rules->unitQuantity(
                $this->identity->audience($request),
                $quantity,
                $variant
            );
            $availableStock = $this->rules->availableStock($product, $variant);

            if ($unitQuantity > $availableStock + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => 'Only '.number_format($availableStock, 3)
                        .' retail packs are available for '.$product->translatedName().'.',
                ]);
            }

            $cart[(string) $lineKey] = [
                'product_id' => (int) $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
            ];
        }

        $this->store($request, $cart);
    }

    public function remove(Request $request, string $lineKey): void
    {
        $cart = $this->cart($request);
        unset($cart[$lineKey]);

        if (ctype_digit($lineKey)) {
            unset($cart[$this->rules->lineKey((int) $lineKey, null)]);
        }

        $this->store($request, $cart);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(StorefrontSessionKeys::CART);
    }

    public function cart(Request $request): array
    {
        $stored = $request->session()->get(StorefrontSessionKeys::CART, []);
        if (! is_array($stored)) {
            return [];
        }

        $cart = [];

        foreach ($stored as $storedKey => $storedValue) {
            if (is_array($storedValue)) {
                $productId = (int) ($storedValue['product_id'] ?? 0);
                $variantId = (int) ($storedValue['variant_id'] ?? 0) ?: null;
                $quantity = round((float) ($storedValue['quantity'] ?? 0), 3);
            } else {
                $productId = (int) $storedKey;
                $variantId = null;
                $quantity = round((float) $storedValue, 3);
            }

            if ($productId > 0 && $quantity > 0) {
                $lineKey = $this->rules->lineKey($productId, $variantId);
                $cart[$lineKey] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                ];
            }
        }

        return $cart;
    }

    private function store(Request $request, array $cart): void
    {
        $request->session()->put(StorefrontSessionKeys::CART, $cart);
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
