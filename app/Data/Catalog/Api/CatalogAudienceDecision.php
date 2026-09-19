<?php

namespace App\Data\Catalog\Api;

final readonly class CatalogAudienceDecision
{
    public function __construct(
        public string $audience,
        public bool $allowed = true,
        public ?string $message = null,
        public int $status = 200
    ) {}
}
