<?php

namespace App\Services\Sales;

use App\Contracts\Sales\SalesDocumentGstDetailsContract;
use App\Contracts\Support\AmountInWordsContract;
use App\Models\Address;
use App\Models\CompanySetting;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * SRP: derive the GST sections of a printed sales document from data the ERP
 * already stores. Nothing here is persisted.
 */
final class SalesDocumentGstDetailsService implements SalesDocumentGstDetailsContract
{
    public function __construct(private readonly AmountInWordsContract $words) {}

    public function parties(Order $order, string $partyName): array
    {
        $isDealer = $order->order_type === 'dealer';
        $account = $isDealer ? $order->dealer : $order->customer;
        $gstin = $isDealer ? (string) ($order->dealer?->dealerProfile?->gst_number ?? '') : '';

        $saved = $this->savedAddress($account);
        $shippingAddress = $this->orderAddress($order) ?: $this->addressLine($saved);

        $shipping = [
            'name' => (string) ($order->contact_name ?: $saved?->name ?: $partyName),
            'mobile' => (string) ($order->contact_mobile ?: $saved?->mobile ?: $account?->mobile ?: ''),
            'gstin' => $gstin,
            'address' => $shippingAddress,
        ];

        $billing = [
            'name' => $partyName,
            'mobile' => (string) ($account?->mobile ?: $shipping['mobile']),
            'gstin' => $gstin,
            'address' => $this->addressLine($saved) ?: $shippingAddress,
        ];

        return [
            'billing' => $billing,
            'shipping' => $shipping,
            'place_of_supply' => (string) ($order->state ?: $saved?->state ?: ''),
        ];
    }

    public function taxSummary(array $items, float $grandTotal, string $placeOfSupply, ?CompanySetting $company): array
    {
        $rows = [];

        foreach ($items as $item) {
            $rate = round((float) $item['gst_percent'], 2);
            $key = (string) $rate;
            $gst = (float) $item['gst_amount'];

            $rows[$key] ??= ['rate' => $rate, 'taxable' => 0.0, 'tax' => 0.0];
            // Line totals are stored GST-inclusive.
            $rows[$key]['taxable'] += (float) $item['line_total'] - $gst;
            $rows[$key]['tax'] += $gst;
        }

        ksort($rows, SORT_NUMERIC);

        return [
            'rows' => array_values($rows),
            'intra_state' => $this->isIntraState($placeOfSupply, $company),
            'taxable' => round(array_sum(array_column($rows, 'taxable')), 2),
            'tax' => round(array_sum(array_column($rows, 'tax')), 2),
            'in_words' => $this->words->rupees($grandTotal),
        ];
    }

    /**
     * The company has no separate state field, so its address text is checked
     * for the supply state. Unknown either side defaults to CGST + SGST.
     */
    private function isIntraState(string $placeOfSupply, ?CompanySetting $company): bool
    {
        $companyAddress = (string) ($company?->address ?? '');

        if (trim($placeOfSupply) === '' || trim($companyAddress) === '') {
            return true;
        }

        return Str::contains(Str::lower($companyAddress), Str::lower(trim($placeOfSupply)));
    }

    private function savedAddress(?User $account): ?Address
    {
        if (! $account) {
            return null;
        }

        $addresses = $account->addresses;

        return $addresses->firstWhere('type', 'billing')
            ?? $addresses->firstWhere('is_default', true)
            ?? $addresses->first();
    }

    private function orderAddress(Order $order): string
    {
        return $this->join([$order->address_line1, $order->address_line2, $order->city, $order->state, $order->pincode]);
    }

    private function addressLine(?Address $address): string
    {
        return $address
            ? $this->join([$address->address_line1, $address->address_line2, $address->city, $address->state, $address->pincode])
            : '';
    }

    /**
     * @param  array<int, mixed>  $parts
     */
    private function join(array $parts): string
    {
        return collect($parts)->map(fn ($part) => trim((string) $part))->filter()->implode(', ');
    }
}
