<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductFormContract;
use App\Contracts\Catalog\Product\ProductImageContract;
use App\Contracts\Catalog\Product\ProductMediaContract;
use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Contracts\Catalog\Product\ProductVariantFormDataContract;
use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Models\Catalog\Product;
use App\Models\Inventory\Warehouse;

final class ProductFormService implements ProductFormContract
{
    public function __construct(
        private readonly ProductImageContract $images,
        private readonly ProductTranslationContract $translations,
        private readonly ProductVariantFormDataContract $variants,
        private readonly ProductMediaContract $media,
        private readonly ProductVariantUnitContract $units,
    ) {}

    public function augmentData(Product $product, array $data): array
    {
        return array_merge(
            $data,
            $this->images->formData($product),
            $this->translations->formData($product),
            ['variants' => $this->variants->rowsFor($product)],
            ['media' => $this->media->formData($product)],
        );
    }

    public function augmentOptions(array $options): array
    {
        $options['variant_warehouses'] = Warehouse::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
        $options['variant_units'] = $this->units->options();

        return $options;
    }
}
