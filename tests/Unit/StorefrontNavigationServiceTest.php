<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Storefront\StorefrontTopbarMessage;
use App\Services\Storefront\StorefrontNavigationService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StorefrontNavigationServiceTest extends TestCase
{
    public function test_navigation_keeps_existing_product_type_and_featured_fallback_shape(): void
    {
        $category = new Category;
        $category->id = 1;
        $type = new Product;
        $type->product_type = 'medicine';
        $type->products_count = 4;
        $fallback = new Product;
        $fallback->id = 5;

        $repository = new class($category, $type, $fallback) implements StorefrontNavigationRepositoryContract
        {
            public function __construct(
                private readonly Category $category,
                private readonly Product $type,
                private readonly Product $fallback
            ) {}

            public function categories(string $audience): Collection
            {
                return collect([$this->category]);
            }

            public function categoryMenu(string $audience, int $categoryLimit, int $productLimit): Collection
            {
                return collect([$this->category]);
            }

            public function productTypeCounts(string $audience): Collection
            {
                return collect([$this->type]);
            }

            public function featuredProducts(string $audience): Collection
            {
                return collect();
            }

            public function fallbackProducts(string $audience): Collection
            {
                return collect([$this->fallback]);
            }

            public function dealProducts(string $audience, int $limit): Collection
            {
                return collect([$this->fallback])->map(fn (Product $product) => $product->setAttribute('deal_limit', $limit)->setAttribute('deal_audience', $audience));
            }

            public function topbarMessages(): Collection
            {
                return collect([new StorefrontTopbarMessage(['message' => 'Now on sale'])]);
            }
        };

        $result = (new StorefrontNavigationService($repository))->data('customer');

        $this->assertSame('Medicine', $result['productTypes']->first()['name']);
        $this->assertSame(4, $result['productTypes']->first()['products_count']);
        $this->assertSame([5], $result['featuredProducts']->pluck('id')->all());
        $this->assertSame([1], $result['categories']->pluck('id')->all());
        $this->assertSame(['Now on sale'], $result['topbarMessages']->pluck('message')->all());
        $this->assertSame([5], $result['dealProducts']->pluck('id')->all());
        $this->assertSame(4, $result['dealProducts']->first()->deal_limit);
        $this->assertSame('customer', $result['dealProducts']->first()->deal_audience);

        $empty = (new StorefrontNavigationService($repository))->emptyData();
        $this->assertTrue($empty['topbarMessages']->isEmpty());
        $this->assertTrue($empty['dealProducts']->isEmpty());
    }
}
