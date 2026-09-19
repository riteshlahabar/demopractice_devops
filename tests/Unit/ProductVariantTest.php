<?php

namespace Tests\Unit;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ProductVariantTest extends TestCase
{
    public function test_main_display_pack_and_case_rate_use_per_retail_pack_values(): void
    {
        $product = new Product([
            'dealer_price' => 80,
            'customer_price' => 100,
        ]);

        $inactive = new ProductVariant([
            'product_id' => 1,
            'value' => '100 ML',
            'dealer_price' => 70,
            'customer_price' => 90,
            'is_default' => true,
            'is_active' => false,
        ]);

        $main = new ProductVariant([
            'product_id' => 1,
            'size_value' => 250,
            'size_unit' => 'ML',
            'value' => '250 ML',
            'units_per_case' => 50,
            'dealer_price' => 82,
            'customer_price' => 110,
            'is_default' => true,
            'is_active' => true,
        ]);
        $main->setRelation('product', $product);

        $product->setRelation('variants', new Collection([$inactive, $main]));

        $this->assertSame($main, $product->mainVariant());
        $this->assertSame('250 ML', $main->display_name);
        $this->assertSame(82.0, $main->priceFor('dealer'));
        $this->assertSame(4100.0, $main->priceFor('dealer') * (float) $main->units_per_case);
    }
}
