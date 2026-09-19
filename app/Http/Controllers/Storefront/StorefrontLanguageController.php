<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontLanguageContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StorefrontLanguageController extends Controller
{
    public function __construct(
        private readonly StorefrontLanguageContract $languages
    ) {}

    public function update(Request $request, string $locale): RedirectResponse
    {
        $this->languages->switchLocale($request, $locale);

        return back();
    }
}
