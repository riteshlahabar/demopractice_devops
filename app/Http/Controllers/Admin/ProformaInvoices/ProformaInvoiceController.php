<?php

namespace App\Http\Controllers\Admin\ProformaInvoices;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProformaInvoiceController extends AdminModuleController
{
    protected string $moduleKey = 'proforma-invoices';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        if (empty($data['proforma_no'])) {
            $data['proforma_no'] = 'PI'.now()->format('ymdHis').random_int(100, 999);
        }

        if (! empty($data['order_id'])) {
            $order = Order::query()->find($data['order_id']);

            if ($order) {
                foreach (['subtotal', 'gst_total', 'discount_total', 'grand_total'] as $amountField) {
                    if ($request->filled($amountField)) {
                        continue;
                    }

                    $data[$amountField] = $order->{$amountField};
                }
            }
        }

        return $data;
    }

    public function convertToInvoice(int|string $id): RedirectResponse
    {
        $proforma = ProformaInvoice::query()->with('order')->findOrFail($id);
        $order = $proforma->order;

        abort_unless($order, 422, 'Proforma Invoice is not linked to a Sale Order.');

        $invoice = DB::transaction(function () use ($proforma, $order): Invoice {
            $invoice = Invoice::query()->firstOrCreate(
                ['order_id' => $order->id],
                [
                    'invoice_no' => 'INV'.now()->format('ymdHis').str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => now()->toDateString(),
                    'grand_total' => $proforma->grand_total ?: $order->grand_total,
                ]
            );

            $proforma->forceFill(['status' => 'converted'])->save();

            // The order status follows the invoice: InvoiceOrderStatusObserver
            // moves it to Approved, so nothing is set here by hand.

            return $invoice;
        });

        return redirect()->route('admin.invoices.edit', $invoice->getKey())->with('success', 'Proforma Invoice converted to Sale Invoice.');
    }
}
