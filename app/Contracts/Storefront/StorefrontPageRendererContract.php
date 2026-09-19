<?php

namespace App\Contracts\Storefront;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

interface StorefrontPageRendererContract
{
    public function render(Request $request, string $page, array $data = []): View;
}
