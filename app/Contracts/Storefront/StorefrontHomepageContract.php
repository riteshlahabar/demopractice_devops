<?php

namespace App\Contracts\Storefront;

interface StorefrontHomepageContract
{
    public function content(string $audience): array;

    public function emptyContent(): array;
}
