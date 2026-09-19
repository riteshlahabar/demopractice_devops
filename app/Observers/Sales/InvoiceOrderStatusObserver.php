<?php

namespace App\Observers\Sales;

use App\Contracts\Sales\OrderStatusContract;
use App\Models\Sales\Invoice;
use Throwable;

/**
 * Raising the sale invoice is the approval step, so the order becomes Approved
 * whether the invoice came from admin, the proforma conversion or the API.
 */
final class InvoiceOrderStatusObserver
{
    public function __construct(
        private readonly OrderStatusContract $status
    ) {}

    public function created(Invoice $invoice): void
    {
        try {
            $order = $invoice->order;

            if ($order !== null) {
                $this->status->invoiceCreated($order);
            }
        } catch (Throwable) {
            // Status will catch up on the next sales action.
        }
    }
}
