<?php

namespace App\Services\Sales;

use App\Contracts\Sales\SalesDocumentDataContract;
use App\Contracts\Sales\SalesDocumentGstDetailsContract;
use App\Models\CompanySetting;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;

/**
 * SRP: turn one sales record into the flat array every invoice view needs.
 *
 * Previously this lived inside the admin controller, so the API had no way to
 * render the same document.
 */
final class SalesDocumentDataService implements SalesDocumentDataContract
{
    private const RELATIONS = [
        'order.items.product.unit',
        'order.items.variant.unit',
        'order.customer.addresses',
        'order.dealer.dealerProfile',
        'order.dealer.addresses',
        'order.salesman',
    ];

    public function __construct(private readonly SalesDocumentGstDetailsContract $gst) {}

    public function forDocument(string $document, int|string $id): array
    {
        [$title, $record, $order, $number, $date, $validUntil, $status] = match ($document) {
            'order' => $this->orderData($id),
            'proforma' => $this->proformaData($id),
            'invoice' => $this->invoiceData($id),
            default => abort(404),
        };

        abort_unless($order, 404, 'Linked Sale Order not found.');

        $isDealer = $order->order_type === 'dealer';

        $party = $isDealer
            ? ($order->dealer?->dealerProfile?->firm_name ?: $order->dealer?->name)
            : $order->customer?->name;

        $party = $party ?: 'Walk-in Customer';
        $company = CompanySetting::query()->first();
        $items = $this->items($order, $isDealer);
        $parties = $this->gst->parties($order, $party);
        $grandTotal = (float) data_get($record, 'grand_total', $order->grand_total);

        return [
            'title' => $title,
            'record' => $record,
            'order' => $order,
            'number' => $number,
            'date' => $date,
            'validUntil' => $validUntil,
            'status' => $status,
            'party' => $party,
            'contact' => $isDealer ? $order->dealer : $order->customer,
            'company' => $company,
            'items' => $items,
            'billing' => $parties['billing'],
            'shipping' => $parties['shipping'],
            'placeOfSupply' => $parties['place_of_supply'],
            'taxSummary' => $this->gst->taxSummary($items, $grandTotal, $parties['place_of_supply'], $company),
            'totals' => [
                'subtotal' => data_get($record, 'subtotal', $order->subtotal),
                'gst_total' => data_get($record, 'gst_total', $order->gst_total),
                'discount_total' => data_get($record, 'discount_total', $order->discount_total),
                'grand_total' => data_get($record, 'grand_total', $order->grand_total),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items(Order $order, bool $isDealer): array
    {
        return $order->items->map(function ($item) use ($isDealer): array {
            $name = ($item->product?->name ?? 'Product')
                .($item->variant_name ? ' - '.$item->variant_name : '');

            if ($isDealer && $item->variant_name) {
                $name .= ' ('.$this->trimNumber((float) $item->units_per_case).' per case)';
            }

            return [
                'name' => $name,
                'hsn_code' => $item->product?->hsn_code,
                'quantity' => $item->pack_quantity ?? $item->quantity,
                'unit' => $this->unitLabel($item, $isDealer),
                'unit_price' => $item->unit_price,
                'gst_percent' => $item->gst_percent,
                'gst_amount' => $item->gst_amount,
                'line_total' => $item->line_total,
            ];
        })->all();
    }

    private function unitLabel($item, bool $isDealer): string
    {
        // Dealer quantities are cases whenever the line came from a variant.
        if ($isDealer && $item->pack_quantity !== null && (float) $item->units_per_case > 0) {
            return 'Case';
        }

        return (string) ($item->variant?->unit?->short_name ?: $item->product?->unit?->short_name ?: 'Pcs.');
    }

    private function trimNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    private function orderData(int|string $id): array
    {
        $order = Order::query()
            ->with(['items.product.unit', 'items.variant.unit', 'customer.addresses', 'dealer.dealerProfile', 'dealer.addresses', 'salesman'])
            ->findOrFail($id);

        return ['Sale Order', $order, $order, $order->order_no, $order->created_at, null, $order->status];
    }

    private function proformaData(int|string $id): array
    {
        $proforma = ProformaInvoice::query()->with(self::RELATIONS)->findOrFail($id);

        return [
            'Proforma Invoice', $proforma, $proforma->order, $proforma->proforma_no,
            $proforma->proforma_date, $proforma->valid_until, $proforma->status,
        ];
    }

    private function invoiceData(int|string $id): array
    {
        $invoice = Invoice::query()->with(self::RELATIONS)->findOrFail($id);

        return [
            'Sale Invoice', $invoice, $invoice->order, $invoice->invoice_no,
            $invoice->invoice_date, null, 'issued',
        ];
    }
}
