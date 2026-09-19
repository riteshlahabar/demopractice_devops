<?php

namespace App\Contracts\Storefront\Session;

use App\Models\Catalog\Product;
use Illuminate\Http\Request;

interface StorefrontWishlistContract
{
    public function add(Request $request, Product $product): void;

    public function remove(Request $request, int $productId): void;

    public function toggle(Request $request, Product $product): bool;

    public function clear(Request $request): void;

    public function wishlist(Request $request): array;

    public function has(Request $request, int $productId): bool;

    public function summary(Request $request): array;
}
