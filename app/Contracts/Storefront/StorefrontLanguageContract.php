<?php

namespace App\Contracts\Storefront;

use Illuminate\Http\Request;

interface StorefrontLanguageContract
{
    public function data(Request $request): array;

    public function emptyData(): array;

    public function switchLocale(Request $request, string $locale): void;
}
