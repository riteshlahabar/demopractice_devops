<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\DealerOrderContextContract;
use App\Contracts\Sales\Orders\OrderCheckoutMapperContract;
use App\Contracts\Sales\Orders\OrderLineBuilderContract;
use App\Contracts\Sales\Orders\OrderNumberGeneratorContract;
use App\Contracts\Sales\Orders\OrderRepositoryContract;
use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Contracts\Sales\Orders\StockReservationContract;
use App\Contracts\Support\TransactionManagerContract;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class OrderWorkflowService implements OrderWorkflowContract
{
    public function __construct(
        private readonly OrderLineBuilderContract $lines,
        private readonly OrderRepositoryContract $orders,
        private readonly StockReservationContract $stock,
        private readonly OrderNumberGeneratorContract $numbers,
        private readonly DealerOrderContextContract $dealers,
        private readonly OrderCheckoutMapperContract $checkout,
        private readonly TransactionManagerContract $transactions
    ) {}

    public function createForCustomer(
        User $customer,
        array $items,
        ?string $notes = null,
        array $checkoutData = []
    ): Order {
        return $this->createOrder(
            'customer',
            $customer,
            null,
            null,
            $items,
            $notes,
            $checkoutData
        );
    }

    public function createForDealer(
        User $dealer,
        array $items,
        ?string $notes = null,
        array $checkoutData = []
    ): Order {
        $salesman = $this->dealers->assignedSalesman($dealer);

        return $this->createOrder(
            'dealer',
            null,
            $dealer,
            $salesman,
            $items,
            $notes,
            $checkoutData
        );
    }

    public function createBySalesman(
        User $salesman,
        User $dealer,
        array $items,
        ?string $notes = null,
        array $checkoutData = []
    ): Order {
        $this->dealers->assertAssignedToSalesman($dealer, $salesman);

        return $this->createOrder(
            'dealer',
            null,
            $dealer,
            $salesman,
            $items,
            $notes,
            $checkoutData
        );
    }

    private function createOrder(
        string $orderType,
        ?User $customer,
        ?User $dealer,
        ?User $salesman,
        array $items,
        ?string $notes,
        array $checkoutData
    ): Order {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'At least one item is required.',
            ]);
        }

        return $this->transactions->run(function () use (
            $orderType,
            $customer,
            $dealer,
            $salesman,
            $items,
            $notes,
            $checkoutData
        ): Order {
            $lineItems = $this->lines->build($orderType, $items);
            $actor = $salesman ?: $dealer ?: $customer;

            $order = $this->orders->create([
                'order_no' => $this->numbers->next($orderType),
                'order_type' => $orderType,
                'customer_id' => $customer?->id,
                'dealer_id' => $dealer?->id,
                'salesman_id' => $salesman?->id,
                'status' => $orderType === 'dealer'
                    ? 'salesman_review'
                    : 'admin_review',
                'notes' => $notes,
                ...$this->checkout->map($checkoutData),
            ]);

            $totals = $this->orders->addItems($order, $lineItems);

            $this->stock->reserve($order, $lineItems, $actor);

            $this->orders->updateTotals(
                $order,
                (float) $totals['subtotal'],
                (float) $totals['gst_total']
            );

            return $this->orders->loadForResult($order);
        });
    }
}
