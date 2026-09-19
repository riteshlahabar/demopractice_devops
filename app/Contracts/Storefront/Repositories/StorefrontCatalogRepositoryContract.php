<?php

namespace App\Contracts\Storefront\Repositories;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\CompanySetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StorefrontCatalogRepositoryContract
{
    public function categories(string $audience, int $limit): Collection;

    public function shopProducts(
        string $audience,
        ?string $productType,
        ?string $search,
        int $perPage
    ): LengthAwarePaginator;

    public function defaultProducts(string $audience, int $limit): Collection;

    public function categoryProducts(Category $category, string $audience, int $perPage): LengthAwarePaginator;

    public function loadProduct(Product $product): void;

    public function fallbackRelatedProducts(Product $product, string $audience, int $limit): Collection;

    public function trendingProducts(Product $product, string $audience, int $limit): Collection;

    public function companySetting(): ?CompanySetting;
}
