<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\ProductCatalogRepositoryContract;
use App\Data\Catalog\Api\ProductCatalogFilters;
use App\Models\Catalog\Product;
use App\Services\Catalog\Api\ProductCatalogService;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ProductCatalogServiceTest extends TestCase
{
    public function test_service_preserves_pagination_payload_and_cache_identity(): void
    {
        $product = new Product;
        $product->id = 42;

        $repository = new class($product) implements ProductCatalogRepositoryContract
        {
            public function __construct(private readonly Product $product) {}

            public function paginate(ProductCatalogFilters $filters): LengthAwarePaginatorContract
            {
                return new LengthAwarePaginator([$this->product], 1, $filters->perPage, $filters->page);
            }
        };

        $presenter = new class implements ProductCatalogPresenterContract
        {
            public function present(Product $product): array
            {
                return ['id' => $product->id];
            }
        };

        $cache = new class implements CatalogCacheContract
        {
            public string $key = '';

            public bool $fresh = false;

            public function version(): int
            {
                return 7;
            }

            public function remember(string $key, bool $fresh, Closure $loader): mixed
            {
                $this->key = $key;
                $this->fresh = $fresh;

                return $loader();
            }
        };

        $filters = new ProductCatalogFilters('dealer', 5, 'spray', 2, 25);
        $result = (new ProductCatalogService($repository, $presenter, $cache))
            ->products($filters, true);

        $this->assertSame([['id' => 42]], $result['data']);
        $this->assertSame(2, $result['current_page']);
        $this->assertTrue($cache->fresh);
        $this->assertSame(
            'catalog.products.7.'.app()->getLocale().'.'.sha1(json_encode($filters->cachePayload())),
            $cache->key
        );
    }
}
