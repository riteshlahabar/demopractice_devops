<?php

namespace Tests\Unit;

use App\Models\Catalog\ProductVariant;
use App\Services\Sales\Orders\OrderLineQuantityService;
use PHPUnit\Framework\TestCase;

class OrderLineQuantityServiceTest extends TestCase
{
    public function test_customer_quantity_one_is_one_retail_pack(): void
    {
        $variant = new ProductVariant([
            'units_per_case' => 24,
        ]);

        $result =
            (new OrderLineQuantityService)
                ->normalize(
                    'customer',
                    ['quantity' => 1],
                    $variant
                );

        $this->assertSame(
            1.0,
            $result['quantity']
        );

        $this->assertSame(
            1.0,
            $result['pack_quantity']
        );
    }

    public function test_dealer_quantity_one_is_one_case(): void
    {
        $variant = new ProductVariant([
            'units_per_case' => 24,
        ]);

        $result =
            (new OrderLineQuantityService)
                ->normalize(
                    'dealer',
                    ['quantity' => 1],
                    $variant
                );

        $this->assertSame(
            24.0,
            $result['quantity']
        );

        $this->assertSame(
            1.0,
            $result['pack_quantity']
        );
    }

    public function test_storefront_dealer_quantity_is_not_converted_twice(): void
    {
        $variant = new ProductVariant([
            'units_per_case' => 24,
        ]);

        $result =
            (new OrderLineQuantityService)
                ->normalize(
                    'dealer',
                    [
                        'quantity' => 48,
                        'pack_quantity' => 2,
                    ],
                    $variant
                );

        $this->assertSame(
            48.0,
            $result['quantity']
        );

        $this->assertSame(
            2.0,
            $result['pack_quantity']
        );
    }
}
