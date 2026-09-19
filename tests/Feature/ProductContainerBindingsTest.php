<?php

namespace Tests\Feature;

use App\Contracts\Catalog\Product\ProductFormContract;
use App\Contracts\Catalog\Product\ProductSkuContract;
use App\Contracts\Catalog\Product\ProductVariantContract;
use App\Contracts\Catalog\Product\ProductVariantFormDataContract;
use App\Contracts\Catalog\Product\ProductVariantProjectionContract;
use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Contracts\Catalog\Product\ProductWorkflowContract;
use App\Http\Controllers\Admin\Products\ProductController;
use App\Models\Catalog\ProductVariant;
use Tests\TestCase;

class ProductContainerBindingsTest extends TestCase
{
    /**
     * The product form is assembled from injected contracts, so a missing
     * binding only shows up when the admin opens the page. Resolving the whole
     * graph here catches it at test time instead.
     */
    public function test_product_contracts_resolve(): void
    {
        foreach ([
            ProductWorkflowContract::class,
            ProductFormContract::class,
            ProductVariantContract::class,
            ProductVariantFormDataContract::class,
            ProductVariantUnitContract::class,
            ProductVariantProjectionContract::class,
            ProductSkuContract::class,
            ProductController::class,
        ] as $abstract) {
            $this->assertInstanceOf($abstract === ProductController::class ? ProductController::class : $abstract, app($abstract));
        }
    }

    public function test_variant_exposes_the_unit_relation_used_by_the_products_listing(): void
    {
        // config/admin.php eager loads `variants.unit` for the products module.
        $this->assertTrue(method_exists(ProductVariant::class, 'unit'));
        $this->assertSame('unit_id', (new ProductVariant)->unit()->getForeignKeyName());
    }
}
