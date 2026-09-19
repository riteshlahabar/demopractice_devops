<?php

namespace App\Data\Catalog\Api;

final readonly class ProductCatalogFilters
{
    public function __construct(
        public string $audience,
        public ?int $categoryId,
        public string $search,
        public int $page,
        public int $perPage
    ) {}

    public function cachePayload(): array
    {
        return [
            'audience' => $this->audience,
            'category_id' => $this->categoryId,
            'search' => $this->search,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ];
    }
}
