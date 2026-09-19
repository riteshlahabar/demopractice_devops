<?php

namespace App\Services\Sales;

use App\Models\Sales\ReturnRequest;
use App\Models\User;
use App\Services\Sales\Access\OrderOwnershipScope;
use RuntimeException;

/**
 * The rules that decide whether a return may be opened, kept out of the
 * controller so both the API and any future admin/storefront path enforce the
 * same window and the same duplicate check.
 */
class ReturnRequestService
{
    /** Days after delivery during which a return may still be raised. */
    private const WINDOW_DAYS = 7;

    /** Only these order states can be returned at all. */
    private const RETURNABLE_STATUSES = ['delivered', 'completed'];

    public function __construct(private readonly OrderOwnershipScope $scope) {}

    public function open(User $user, array $input): ReturnRequest
    {
        $order = $this->scope->find($user, (int) $input['order_id'], ['dispatches']);

        if ($order === null) {
            throw new RuntimeException('Order not found.');
        }

        if (! in_array($order->status, self::RETURNABLE_STATUSES, true)) {
            throw new RuntimeException('This order is not eligible for return yet.');
        }

        if (ReturnRequest::query()->where('order_id', $order->id)->exists()) {
            throw new RuntimeException('A return request already exists for this order.');
        }

        $deliveredAt = $order->dispatches->max('delivered_at');

        if ($deliveredAt !== null && $deliveredAt->diffInDays(now()) > self::WINDOW_DAYS) {
            throw new RuntimeException(
                'The '.self::WINDOW_DAYS.'-day return window for this order has closed.'
            );
        }

        // The refund is capped at the order total: a client-supplied amount is
        // a request, never an instruction.
        $refund = min(
            (float) ($input['refund_amount'] ?? $order->grand_total),
            (float) $order->grand_total
        );

        return ReturnRequest::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'return_no' => 'RET'.now()->format('ymdHis').random_int(100, 999),
            'reason' => $input['reason'],
            'refund_amount' => $refund,
            'status' => 'requested',
        ]);
    }
}
