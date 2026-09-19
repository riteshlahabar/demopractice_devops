<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductImageContract;
use App\Contracts\Files\PublicUploadContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;

final class ProductImageService implements ProductImageContract
{
    public function __construct(private readonly PublicUploadContract $uploads) {}

    public function sync(Product $product, ?string $primaryImagePath, array $galleryPaths, array $removeGalleryIds): void
    {
        if ($primaryImagePath) {
            ProductImage::query()->where('product_id', $product->getKey())->update(['is_primary' => false]);
            ProductImage::query()->create([
                'product_id' => $product->getKey(), 'path' => $primaryImagePath,
                'is_primary' => true, 'sort_order' => 0,
            ]);
        }

        if ($removeGalleryIds !== []) {
            $images = ProductImage::query()->where('product_id', $product->getKey())
                ->where('is_primary', false)->whereIn('id', $removeGalleryIds)->get();
            foreach ($images as $image) {
                $path = $image->path;
                $image->delete();
                $this->uploads->delete($path);
            }
        }

        if ($galleryPaths !== []) {
            $startOrder = (int) ProductImage::query()->where('product_id', $product->getKey())->max('sort_order');
            foreach ($galleryPaths as $index => $path) {
                ProductImage::query()->create([
                    'product_id' => $product->getKey(), 'path' => $path,
                    'is_primary' => false, 'sort_order' => $startOrder + $index + 1,
                ]);
            }
        }
    }

    public function destroyGalleryImage(Product $product, ProductImage $image): void
    {
        abort_unless((int) $image->product_id === (int) $product->getKey(), 404);
        $path = $image->path;
        $image->delete();
        $this->uploads->delete($path);
    }

    public function destroyFieldImage(Product $product, string $field): void
    {
        $path = $product->{$field};
        $product->forceFill([$field => null])->save();
        $this->uploads->delete($path);
    }

    public function formData(Product $product): array
    {
        $primary = $product->relationLoaded('images')
            ? $product->images->firstWhere('is_primary', true)
            : $product->images()->where('is_primary', true)->first();

        return ['primary_image' => $primary?->path, 'primary_image_id' => $primary?->getKey()];
    }
}
