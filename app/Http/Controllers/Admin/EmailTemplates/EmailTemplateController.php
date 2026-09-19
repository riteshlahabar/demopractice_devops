<?php

namespace App\Http\Controllers\Admin\EmailTemplates;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email-templates.index', [
            'pageTitle' => 'Email Templates',
            'breadcrumbs' => ['Settings', 'Email Templates'],
            'templates' => config('storefront.email_templates', []),
        ]);
    }

    public function show(string $template): View
    {
        abort_unless(in_array($template, config('storefront.email_templates', []), true), 404);

        return view('emails.fastkart.'.$template, [
            'recipientName' => 'Customer', 'orderNumber' => 'CO-DEMO-1001',
            'resetUrl' => route('store.page', ['page' => 'forgot']),
            'offerTitle' => 'Special Farmer Medicine Offer',
        ]);
    }
}
