<?php

namespace App\Contracts\Storefront;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * SRP: everything the website FAQ page needs — the questions to show, the
 * search term and category the visitor filtered by, and the category cards.
 */
interface StorefrontFaqContract
{
    /**
     * @return array{faqs: Collection, faqSearch: string|null, faqCategory: string|null, faqCategories: array<int, array{key: string, label: string, description: string, image: string, count: int, active: bool}>}
     */
    public function pageData(Request $request): array;
}
