<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\Catalog\CategoryCatalogController;
use App\Http\Controllers\Api\Catalog\HomepageCatalogController;
use App\Http\Controllers\Api\Catalog\ProductCatalogController;
use App\Http\Controllers\Api\Catalog\TranslationCatalogController;
use Tests\TestCase;

class CatalogServiceProviderTest extends TestCase
{
    public function test_all_catalog_route_controllers_resolve_from_the_container(): void
    {
        $this->assertInstanceOf(
            CategoryCatalogController::class,
            $this->app->make(CategoryCatalogController::class)
        );
        $this->assertInstanceOf(
            HomepageCatalogController::class,
            $this->app->make(HomepageCatalogController::class)
        );
        $this->assertInstanceOf(
            ProductCatalogController::class,
            $this->app->make(ProductCatalogController::class)
        );
        $this->assertInstanceOf(
            TranslationCatalogController::class,
            $this->app->make(TranslationCatalogController::class)
        );
    }
}
