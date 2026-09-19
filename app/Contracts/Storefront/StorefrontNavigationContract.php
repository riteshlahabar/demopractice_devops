<?php

namespace App\Contracts\Storefront;

interface StorefrontNavigationContract
{
    public function data(string $audience): array;

    public function emptyData(): array;
}
