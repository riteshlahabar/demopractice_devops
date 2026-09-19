<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\StorefrontCartContract;
use App\Contracts\Storefront\Session\StorefrontCartStorageContract;
use App\Contracts\Storefront\Session\StorefrontCartSummaryContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Http\Request;

final class StorefrontCartService implements StorefrontCartContract
{
    public function __construct(
        private readonly StorefrontCartStorageContract $storage,
        private readonly StorefrontCartSummaryContract $summaries
    ) {}

    public function add(
        Request $request,
        Product $product,
        float $quantity,
        ?ProductVariant $variant = null
    ): void {
        $this->storage->add($request, $product, $quantity, $variant);
    }

    public function update(Request $request, array $items): void
    {
        $this->storage->update($request, $items);
    }

    public function remove(Request $request, string $lineKey): void
    {
        $this->storage->remove($request, $lineKey);
    }

    public function clear(Request $request): void
    {
        $this->storage->clear($request);
    }

    public function cart(Request $request): array
    {
        return $this->storage->cart($request);
    }

    public function summary(Request $request): array
    {
        return $this->summaries->summary($request);
    }

    public function checkoutItems(Request $request): array
    {
        return $this->summaries->checkoutItems($request);
    }
}
