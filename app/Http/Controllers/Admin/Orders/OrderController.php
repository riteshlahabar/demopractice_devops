<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Contracts\Sales\OrderStatusContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends AdminModuleController
{
    protected string $moduleKey = 'orders';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);
        $type = (string) ($data['order_type'] ?? 'customer');

        if (empty($data['order_no'])) {
            $data['order_no'] = $this->nextOrderNumber($type);
        }

        if ($type === 'customer') {
            $data['dealer_id'] = null;
            $data['salesman_id'] = null;
        }

        if ($type === 'dealer') {
            $data['customer_id'] = null;

            if (empty($data['salesman_id']) && ! empty($data['dealer_id'])) {
                $dealer = User::query()->with('dealerProfile')->find($data['dealer_id']);
                $data['salesman_id'] = $dealer?->dealerProfile?->salesman_id;
            }
        }

        $subtotal = (float) ($data['subtotal'] ?? 0);
        $gstTotal = (float) ($data['gst_total'] ?? 0);
        $discount = (float) ($data['discount_total'] ?? 0);

        if (! isset($data['grand_total']) || $data['grand_total'] === '') {
            $data['grand_total'] = max(0, $subtotal + $gstTotal - $discount);
        }

        // Status is never taken from the form; it follows the sales actions.
        unset($data['status']);

        return $data;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        // A new order starts where the workflow starts; an edit keeps the
        // status the sales actions have already given it.
        if ($record === null) {
            $data['status'] = ($data['order_type'] ?? 'customer') === 'dealer' ? 'salesman_review' : 'admin_review';
        }

        return parent::persist($data, $record);
    }

    public function convertToProforma(int|string $id): RedirectResponse
    {
        $order = Order::query()->findOrFail($id);

        $proforma = ProformaInvoice::query()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'proforma_no' => 'PI'.now()->format('ymdHis').str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'proforma_date' => now()->toDateString(),
                'valid_until' => now()->addDays((int) config('admin.sales.proforma_valid_days', 15))->toDateString(),
                'subtotal' => $order->subtotal,
                'gst_total' => $order->gst_total,
                'discount_total' => $order->discount_total,
                'grand_total' => $order->grand_total,
                'status' => 'draft',
                'notes' => $order->notes,
            ]
        );

        return redirect()->route('admin.proforma-invoices.edit', $proforma->getKey())->with('success', 'Sale Order converted to Proforma Invoice.');
    }

    /**
     * The only status an admin sets by hand. Everything else follows the sales
     * actions (invoice raised, dispatch packed / dispatched / delivered).
     */
    public function cancel(Request $request, OrderStatusContract $status, int|string $id): RedirectResponse
    {
        $reason = $request->validate(['cancel_reason' => ['required', 'string', 'max:500']])['cancel_reason'];
        $order = Order::query()->findOrFail($id);

        if (! $status->cancel($order, $reason, auth()->id())) {
            return back()->with('error', 'A delivered or already cancelled order cannot be cancelled.');
        }

        return back()->with('success', 'Order cancelled.');
    }

    private function nextOrderNumber(string $type): string
    {
        return ($type === 'dealer' ? 'DO' : 'CO').now()->format('ymdHis').random_int(100, 999);
    }
}
