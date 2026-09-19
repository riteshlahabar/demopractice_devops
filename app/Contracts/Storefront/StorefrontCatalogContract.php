<?php

namespace App\Contracts\Storefront;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\CompanySetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StorefrontCatalogContract
{
    public function categories(string $audience): Collection;

    public function shopProducts(string $audience, ?string $productType, ?string $search): LengthAwarePaginator;

    public function defaultProducts(string $audience): Collection;

    public function categoryProducts(Category $category, string $audience): LengthAwarePaginator;

    public function productDetails(Product $product, string $audience): array;

    public function companySetting(): ?CompanySetting;
}
