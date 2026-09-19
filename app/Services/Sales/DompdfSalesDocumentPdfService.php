<?php

namespace App\Services\Sales;

use App\Contracts\Sales\SalesDocumentPdfContract;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * SRP: Blade -> PDF bytes, nothing else.
 *
 * The on-screen invoice templates use Bootstrap flex/grid, which dompdf cannot
 * lay out, so the PDF has its own table-based view instead of reusing them.
 */
final class DompdfSalesDocumentPdfService implements SalesDocumentPdfContract
{
    public function render(array $data): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml(View::make('invoices.pdf.document', $data)->render(), 'UTF-8');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    public function filename(array $data): string
    {
        return Str::slug(($data['title'] ?? 'document').'-'.($data['number'] ?? '')).'.pdf';
    }
}
