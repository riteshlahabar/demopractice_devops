<?php

namespace Tests\Unit;

use App\Models\Catalog\Product;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Sales\Orders\OrderItemImageAttacher;
use Tests\TestCase;

class OrderItemImageAttacherTest extends TestCase
{
    public function test_items_get_their_product_image_url(): void
    {
        $product = (new Product)->forceFill(['homepage_image_path' => 'uploads/products/sample.png']);
        $product->setRelation('images', collect());

        $item = new OrderItem;
        $item->setRelation('product', $product);

        (new OrderItemImageAttacher)->attach([$this->orderWith($item)]);

        $this->assertSame(asset('uploads/products/sample.png'), $item->product_image_url);
        $this->assertArrayHasKey('product_image_url', $item->toArray());
    }

    public function test_items_without_a_product_get_null(): void
    {
        $item = new OrderItem;
        $item->setRelation('product', null);

        (new OrderItemImageAttacher)->attach([$this->orderWith($item)]);

        $this->assertNull($item->product_image_url);
    }

    private function orderWith(OrderItem $item): Order
    {
        $order = new Order;
        $order->setRelation('items', collect([$item]));

        return $order;
    }
}
