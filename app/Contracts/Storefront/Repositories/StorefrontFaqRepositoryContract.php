<?php

namespace App\Contracts\Storefront\Repositories;

use Illuminate\Support\Collection;

interface StorefrontFaqRepositoryContract
{
    /**
     * Active questions in display order, optionally narrowed by category and
     * by a search term matched against the question and the answer.
     */
    public function published(?string $category = null, ?string $search = null): Collection;

    /**
     * Number of active questions per category key.
     *
     * @return array<string, int>
     */
    public function countsByCategory(): array;
}
