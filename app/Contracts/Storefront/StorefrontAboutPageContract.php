<?php

namespace App\Contracts\Storefront;

use App\Models\Storefront\StorefrontAboutPage;
use Illuminate\Support\Collection;

/**
 * SRP: the content of the website About Us page - intro block, feature
 * bullets, "What We Do" figures and the team members.
 */
interface StorefrontAboutPageContract
{
    /**
     * @return array{aboutPage: StorefrontAboutPage|null, aboutHighlights: Collection, aboutStats: Collection, aboutTeam: Collection}
     */
    public function pageData(): array;
}
