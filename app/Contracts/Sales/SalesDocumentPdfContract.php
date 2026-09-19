<?php

namespace App\Contracts\Sales;

/**
 * Renders a prepared sales document payload as a real, styled PDF.
 */
interface SalesDocumentPdfContract
{
    /**
     * @param  array<string, mixed>  $data  payload from SalesDocumentDataContract
     * @return string raw PDF bytes
     */
    public function render(array $data): string;

    /**
     * @param  array<string, mixed>  $data
     */
    public function filename(array $data): string;
}
