<?php

namespace App\Contracts\Admin\Imports;

use App\Models\Catalog\Product;

/**
 * SRP: attaching imported images to a product and reflecting the product on
 * the storefront row it selected.
 */
interface ProductImportMediaContract
{
    public function syncPrimaryImage(Product $product, string $path): void;

    /**
     * @param  array<int, string>  $paths
     */
    public function syncGalleryImages(Product $product, array $paths): void;

    public function syncHomepageDisplay(Product $product): void;
}
