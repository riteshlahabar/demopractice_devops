<?php

namespace App\Data\Catalog;

final readonly class ProductSaveData
{
    public function __construct(
        public array $product,
        public ?string $primaryImagePath,
        public array $galleryImagePaths,
        public array $removeGalleryImageIds,
        public ?array $openingStock,
        public array $translations,
        public array $variants,
        public array $media,
    ) {}
}
