<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class StorefrontPreviewController extends Controller
{
    public function invoice(string $template): View
    {
        abort_unless(in_array($template, config('storefront.invoice_templates', []), true), 404);

        return view('invoices.fastkart-preview.'.$template);
    }

    public function email(string $template): View
    {
        abort_unless(in_array($template, config('storefront.email_templates', []), true), 404);

        return view('emails.fastkart.'.$template, [
            'recipientName' => 'Customer',
            'orderNumber' => 'CO-DEMO-1001',
            'resetUrl' => route('store.page', ['page' => 'forgot']),
            'offerTitle' => 'Special Farmer Medicine Offer',
        ]);
    }
}
