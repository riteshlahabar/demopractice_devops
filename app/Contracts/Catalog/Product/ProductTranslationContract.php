<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

interface ProductTranslationContract
{
    public function translatePayload(string $name, ?string $description): array;

    public function extract(array &$data): array;

    public function sync(Product $product, array $translations): void;

    public function formData(Product $product): array;
}
