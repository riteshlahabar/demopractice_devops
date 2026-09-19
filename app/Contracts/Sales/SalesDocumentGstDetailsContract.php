<?php

namespace App\Contracts\Sales;

use App\Models\CompanySetting;
use App\Models\Sales\Order;

/**
 * The GST-format parts of a sales document: billing/shipping blocks, place of
 * supply, the CGST/SGST or IGST split and the total in words.
 */
interface SalesDocumentGstDetailsContract
{
    /**
     * @return array{billing: array<string, string>, shipping: array<string, string>, place_of_supply: string}
     */
    public function parties(Order $order, string $partyName): array;

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{rows: array<int, array<string, float>>, intra_state: bool, taxable: float, tax: float, in_words: string}
     */
    public function taxSummary(array $items, float $grandTotal, string $placeOfSupply, ?CompanySetting $company): array;
}
