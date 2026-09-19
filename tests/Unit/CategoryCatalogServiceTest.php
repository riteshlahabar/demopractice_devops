<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\CategoryCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Services\Catalog\Api\CategoryCatalogService;
use Closure;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class CategoryCatalogServiceTest extends TestCase
{
    public function test_service_uses_replaceable_dependencies(): void
    {
        $repository =
            new class implements CategoryCatalogRepositoryContract
            {
                public function activeForCatalog(
                    string $locale,
                    string $audience
                ): Collection {
                    $category = new Category;
                    $category->id = 10;

                    return collect([
                        $category,
                    ]);
                }
            };

        $presenter =
            new class implements CategoryCatalogPresenterContract
            {
                public function present(
                    Category $category
                ): array {
                    return [
                        'id' => $category->id,
                    ];
                }
            };

        $cache =
            new class implements CatalogCacheContract
            {
                public function version(): int
                {
                    return 1;
                }

                public function remember(
                    string $key,
                    bool $fresh,
                    Closure $loader
                ): mixed {
                    return $loader();
                }
            };

        $service =
            new CategoryCatalogService(
                $repository,
                $presenter,
                $cache
            );

        $result =
            $service->categories(
                'en',
                'customer',
                true
            );

        $this->assertSame(
            [
                [
                    'id' => 10,
                ],
            ],
            $result
        );
    }
}
