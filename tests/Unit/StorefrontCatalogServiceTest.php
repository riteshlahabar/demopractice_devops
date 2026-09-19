<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\CompanySetting;
use App\Services\Storefront\StorefrontCatalogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class StorefrontCatalogServiceTest extends TestCase
{
    public function test_product_page_workflow_uses_repository_fallbacks_without_changing_view_keys(): void
    {
        $product = new Product;
        $product->id = 1;
        $related = new Product;
        $related->id = 2;
        $trending = new Product;
        $trending->id = 3;
        $company = new CompanySetting;

        $repository = new class($related, $trending, $company) implements StorefrontCatalogRepositoryContract
        {
            public function __construct(
                private readonly Product $related,
                private readonly Product $trending,
                private readonly CompanySetting $company
            ) {}

            public function categories(string $audience, int $limit): Collection
            {
                return collect();
            }

            public function shopProducts(
                string $audience,
                ?string $productType,
                ?string $search,
                int $perPage
            ): LengthAwarePaginatorContract {
                return new LengthAwarePaginator([], 0, $perPage);
            }

            public function defaultProducts(string $audience, int $limit): Collection
            {
                return collect();
            }

            public function categoryProducts(
                Category $category,
                string $audience,
                int $perPage
            ): LengthAwarePaginatorContract {
                return new LengthAwarePaginator([], 0, $perPage);
            }

            public function loadProduct(Product $product): void
            {
                $product->setRelation('relatedProductLinks', collect());
            }

            public function fallbackRelatedProducts(Product $product, string $audience, int $limit): Collection
            {
                return collect([$this->related]);
            }

            public function trendingProducts(Product $product, string $audience, int $limit): Collection
            {
                return collect([$this->trending]);
            }

            public function companySetting(): ?CompanySetting
            {
                return $this->company;
            }
        };

        $result = (new StorefrontCatalogService($repository))->productDetails($product, 'customer');

        $this->assertSame($product, $result['storeProduct']);
        $this->assertSame([2], $result['relatedProducts']->pluck('id')->all());
        $this->assertSame([3], $result['trendingProducts']->pluck('id')->all());
        $this->assertSame($company, $result['companySetting']);
    }
}
