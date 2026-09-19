<?php

namespace App\Contracts\Sales;

/**
 * Builds the printable payload for a sale order, proforma invoice or invoice.
 *
 * Shared by the admin print/PDF screens and the customer/dealer API so a
 * document always reads the same wherever it is rendered.
 */
interface SalesDocumentDataContract
{
    /**
     * @param  'order'|'proforma'|'invoice'  $document
     * @return array<string, mixed>
     */
    public function forDocument(string $document, int|string $id): array;
}
