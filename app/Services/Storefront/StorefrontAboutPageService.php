<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontAboutRepositoryContract;
use App\Contracts\Storefront\StorefrontAboutPageContract;
use App\Models\Storefront\StorefrontAboutItem;
use Throwable;

final class StorefrontAboutPageService implements StorefrontAboutPageContract
{
    public function __construct(
        private readonly StorefrontAboutRepositoryContract $about
    ) {}

    public function pageData(): array
    {
        try {
            return [
                'aboutPage' => $this->about->page(),
                'aboutHighlights' => $this->about->items(StorefrontAboutItem::BLOCK_HIGHLIGHT),
                'aboutStats' => $this->about->items(StorefrontAboutItem::BLOCK_STAT),
                'aboutTeam' => $this->about->teamMembers(),
            ];
        } catch (Throwable) {
            return ['aboutPage' => null, 'aboutHighlights' => collect(), 'aboutStats' => collect(), 'aboutTeam' => collect()];
        }
    }
}
