<?php

namespace App\Contracts\Storefront\Session;

use Illuminate\Http\Request;

interface StorefrontCartSummaryContract
{
    public function summary(Request $request): array;

    public function checkoutItems(Request $request): array;
}
