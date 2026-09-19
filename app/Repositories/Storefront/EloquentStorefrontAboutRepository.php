<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontAboutRepositoryContract;
use App\Models\Storefront\StorefrontAboutItem;
use App\Models\Storefront\StorefrontAboutPage;
use App\Models\Storefront\StorefrontTeamMember;
use Illuminate\Support\Collection;

final class EloquentStorefrontAboutRepository implements StorefrontAboutRepositoryContract
{
    public function page(): ?StorefrontAboutPage
    {
        return StorefrontAboutPage::query()->first();
    }

    public function items(string $block): Collection
    {
        return StorefrontAboutItem::query()
            ->where('block', $block)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function teamMembers(): Collection
    {
        return StorefrontTeamMember::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
