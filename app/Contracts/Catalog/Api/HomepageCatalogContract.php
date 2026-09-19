<?php

namespace App\Contracts\Catalog\Api;

interface HomepageCatalogContract
{
    public function homepage(string $audience): array;
}
