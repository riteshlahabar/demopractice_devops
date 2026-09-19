<?php

namespace App\Http\Controllers\Admin\SalesDocuments;

use App\Contracts\Sales\SalesDocumentDataContract;
use App\Contracts\Sales\SalesDocumentPdfContract;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class SalesDocumentController extends Controller
{
    public function __construct(
        private readonly SalesDocumentDataContract $documents,
        private readonly SalesDocumentPdfContract $pdf
    ) {}

    public function print(string $document, int|string $id): View
    {
        $template = max(1, min(3, (int) request()->integer('template', 1)));

        return view(
            'admin.sales-documents.invoice-'.$template,
            $this->documents->forDocument($document, $id)
        );
    }

    public function pdf(string $document, int|string $id): Response
    {
        $data = $this->documents->forDocument($document, $id);

        return response($this->pdf->render($data), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->pdf->filename($data).'"',
        ]);
    }
}
