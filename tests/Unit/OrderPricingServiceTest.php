<?php

namespace Tests\Unit;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Services\Sales\Orders\OrderPricingService;
use PHPUnit\Framework\TestCase;

class OrderPricingServiceTest extends TestCase
{
    public function test_variant_price_remains_gst_inclusive(): void
    {
        $product = new Product([
            'gst_percent' => 18,
            'dealer_price' => 90,
            'customer_price' => 100,
        ]);

        $variant = new ProductVariant([
            'customer_price' => 118,
            'dealer_price' => 118,
        ]);

        $result = (new OrderPricingService)->calculate(
            'customer',
            $product,
            $variant,
            1
        );

        $this->assertSame(118.0, $result['line_total']);
        $this->assertSame(100.0, $result['line_base']);
        $this->assertSame(18.0, $result['gst_amount']);
    }

    public function test_legacy_product_price_keeps_gst_addition_behavior(): void
    {
        $product = new Product([
            'gst_percent' => 18,
            'customer_price' => 100,
            'dealer_price' => 90,
        ]);

        $result = (new OrderPricingService)->calculate(
            'customer',
            $product,
            null,
            1
        );

        $this->assertSame(118.0, $result['line_total']);
        $this->assertSame(100.0, $result['line_base']);
        $this->assertSame(18.0, $result['gst_amount']);
    }
}
