<?php

namespace App\Contracts\Storefront\Session;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Http\Request;

interface StorefrontCartContract
{
    public function add(
        Request $request,
        Product $product,
        float $quantity,
        ?ProductVariant $variant = null
    ): void;

    public function update(Request $request, array $items): void;

    public function remove(Request $request, string $lineKey): void;

    public function clear(Request $request): void;

    public function cart(Request $request): array;

    public function summary(Request $request): array;

    public function checkoutItems(Request $request): array;
}
