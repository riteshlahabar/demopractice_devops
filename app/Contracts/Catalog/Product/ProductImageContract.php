<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;

interface ProductImageContract
{
    public function sync(Product $product, ?string $primaryImagePath, array $galleryPaths, array $removeGalleryIds): void;

    public function destroyGalleryImage(Product $product, ProductImage $image): void;

    public function destroyFieldImage(Product $product, string $field): void;

    public function formData(Product $product): array;
}
