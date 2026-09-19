<?php

namespace App\Contracts\Storefront\Repositories;

use App\Models\Storefront\StorefrontAboutPage;
use Illuminate\Support\Collection;

interface StorefrontAboutRepositoryContract
{
    /** The single About page settings row, or null when it has not been created yet. */
    public function page(): ?StorefrontAboutPage;

    /** Active items of one block ("highlight" or "stat") in display order. */
    public function items(string $block): Collection;

    /** Active team members in display order. */
    public function teamMembers(): Collection;
}
