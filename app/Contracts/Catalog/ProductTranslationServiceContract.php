<?php

namespace App\Contracts\Catalog;

use App\Models\Catalog\Product;

/**
 * Controller-facing Product translation use-case contract.
 *
 * OCP:
 * Alternative implementation can be added without modifying controller.
 *
 * LSP:
 * Any implementation respecting this contract can replace the current one.
 */
interface ProductTranslationServiceContract
{
    public function translatePayload(
        string $name,
        ?string $description
    ): array;

    public function extract(array &$data): array;

    public function sync(
        Product $product,
        array $translations
    ): void;

    public function formData(Product $product): array;
}
