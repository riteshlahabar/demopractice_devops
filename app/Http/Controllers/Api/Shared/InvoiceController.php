<?php

namespace App\Http\Controllers\Api\Shared;

use App\Contracts\Sales\SalesDocumentDataContract;
use App\Contracts\Sales\SalesDocumentPdfContract;
use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Invoice;
use App\Services\Sales\Access\OrderOwnershipScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Invoices belonging to the authenticated account's own orders.
 */
class InvoiceController extends ApiController
{
    public function __construct(
        private readonly OrderOwnershipScope $scope,
        private readonly SalesDocumentDataContract $documents,
        private readonly SalesDocumentPdfContract $pdf
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $invoices = Invoice::query()
            ->whereIn('order_id', $this->scope->forUser($user)->select('id'))
            ->with('order:id,order_no,status,grand_total')
            ->latest('invoice_date')
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        return $this->success([
            'invoices' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function show(Request $request, int $invoice): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $model = Invoice::query()
            ->whereIn('order_id', $this->scope->forUser($user)->select('id'))
            ->with('order.items.product:id,name,sku')
            ->whereKey($invoice)
            ->first();

        if ($model === null) {
            return $this->fail('Invoice not found.', 404);
        }

        return $this->success(['invoice' => $model]);
    }

    /**
     * The same styled PDF the admin prints, scoped to the caller's own orders.
     */
    public function pdf(Request $request, int $invoice): HttpResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $owned = Invoice::query()
            ->whereIn('order_id', $this->scope->forUser($user)->select('id'))
            ->whereKey($invoice)
            ->exists();

        if (! $owned) {
            return $this->fail('Invoice not found.', 404);
        }

        $data = $this->documents->forDocument('invoice', $invoice);

        return response($this->pdf->render($data), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->pdf->filename($data).'"',
        ]);
    }
}
