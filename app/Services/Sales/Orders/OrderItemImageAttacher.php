<?php

namespace App\Services\Sales\Orders;

use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;

/**
 * Adds `product_image_url` to each order item so the apps can show product
 * thumbnails on the order list and order detail screens.
 *
 * Callers eager-load `items.product.images` first, so this never runs one
 * query per item.
 */
final class OrderItemImageAttacher
{
    /**
     * @param  iterable<Order>  $orders
     */
    public function attach(iterable $orders): void
    {
        foreach ($orders as $order) {
            $order->items->each(function (OrderItem $item): void {
                $url = $item->product?->storefront_image_url;

                $item->setAttribute('product_image_url', filled($url) ? $url : null);
            });
        }
    }
}
