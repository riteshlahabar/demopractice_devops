<?php

namespace App\Repositories\Storefront\Session;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionProductRepositoryContract;
use App\Models\Catalog\Product;
use Illuminate\Support\Collection;

final class EloquentStorefrontSessionProductRepository implements StorefrontSessionProductRepositoryContract
{
    public function visibleByIds(string $audience, array $productIds): Collection
    {
        return Product::query()
            ->with([
                'category.translations',
                'brand',
                'unit',
                'images',
                'translations',
                'inventoryBatches',
                'variants.inventoryBatches',
            ])
            ->visibleFor($audience)
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');
    }
}
