<?php

namespace App\Contracts\Storefront\Repositories;

use Illuminate\Support\Collection;

interface StorefrontNavigationRepositoryContract
{
    public function categories(string $audience): Collection;

    public function categoryMenu(string $audience, int $categoryLimit, int $productLimit): Collection;

    public function productTypeCounts(string $audience): Collection;

    public function featuredProducts(string $audience): Collection;

    public function fallbackProducts(string $audience): Collection;

    /** Products ticked "Deal Timer Product", for the header "Deal Today" popup. */
    public function dealProducts(string $audience, int $limit): Collection;

    /** Active header top-bar messages in sort order. */
    public function topbarMessages(): Collection;
}
