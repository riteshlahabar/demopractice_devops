<?php

namespace Tests\Unit;

use App\Models\Sales\Order;
use App\Services\Sales\Orders\OrderStatusService;
use ReflectionMethod;
use Tests\TestCase;

class OrderStatusServiceTest extends TestCase
{
    public function test_each_dispatch_stage_maps_to_an_order_status(): void
    {
        $stage = new ReflectionMethod(OrderStatusService::class, 'dispatchStage');
        $service = new OrderStatusService;

        $cases = [
            ['packing', false, false, false, 'packing'],
            ['dispatched', false, false, false, 'dispatched'],
            ['in_transit', false, false, false, 'dispatched'],
            ['packing', true, false, false, 'dispatched'],
            ['packing', true, true, false, 'out_for_delivery'],
            ['out_for_delivery', false, false, false, 'out_for_delivery'],
            ['packing', true, true, true, 'delivered'],
            ['delivered', false, false, false, 'delivered'],
        ];

        foreach ($cases as [$dispatchStatus, $dispatched, $outForDelivery, $delivered, $expected]) {
            $this->assertSame(
                $expected,
                $stage->invoke($service, $dispatchStatus, $dispatched, $outForDelivery, $delivered),
                $dispatchStatus
            );
        }
    }

    public function test_the_flow_only_runs_forward_and_stops_at_cancelled(): void
    {
        $isForward = new ReflectionMethod(OrderStatusService::class, 'isForward');
        $service = new OrderStatusService;

        $this->assertTrue($isForward->invoke($service, 'admin_review', 'approved'));
        $this->assertTrue($isForward->invoke($service, 'approved', 'delivered'));

        // Backwards and repeated steps are refused, so a re-saved dispatch
        // cannot pull a delivered order back to Dispatched.
        $this->assertFalse($isForward->invoke($service, 'delivered', 'dispatched'));
        $this->assertFalse($isForward->invoke($service, 'approved', 'approved'));
        $this->assertFalse($isForward->invoke($service, 'cancelled', 'approved'));
        $this->assertFalse($isForward->invoke($service, 'admin_review', 'cancelled'));
    }

    public function test_move_to_refuses_an_unknown_status(): void
    {
        $order = new Order(['status' => 'admin_review']);

        $this->assertFalse((new OrderStatusService)->moveTo($order, 'nonsense'));
        $this->assertSame('admin_review', $order->status);
    }

    public function test_a_delivered_or_cancelled_order_cannot_be_cancelled(): void
    {
        $service = new OrderStatusService;

        $delivered = new Order(['status' => 'delivered']);
        $cancelled = new Order(['status' => 'cancelled']);

        $this->assertFalse($service->cancel($delivered, 'Customer changed mind', 1));
        $this->assertFalse($service->cancel($cancelled, 'Duplicate order', 1));
        $this->assertSame('delivered', $delivered->status);
    }

    public function test_the_flow_order_matches_the_admin_status_options(): void
    {
        $options = array_keys(config('admin.modules.orders.status_options'));

        $this->assertSame(array_merge(OrderStatusService::FLOW, [OrderStatusService::CANCELLED]), $options);
    }
}
