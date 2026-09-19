<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductInputContract;
use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Contracts\Files\PublicUploadContract;
use App\Data\Catalog\ProductSaveData;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

final class ProductInputService implements ProductInputContract
{
    public function __construct(
        private readonly PublicUploadContract $uploads,
        private readonly ProductTranslationContract $translations,
    ) {}

    public function make(array $prepared, array $input, array $files, array $module): ProductSaveData
    {
        $variants = (array) ($prepared['variants'] ?? []);
        $media = (array) ($prepared['media'] ?? []);
        unset($prepared['variants'], $prepared['media']);

        foreach ($media as $index => &$row) {
            foreach (['file', 'thumbnail'] as $key) {
                $file = $files['media'][$index][$key] ?? null;
                if ($file instanceof UploadedFile) {
                    $row[$key] = $file;
                }
            }
        }
        unset($row);

        $primaryImagePath = $prepared['primary_image'] ?? null;
        unset($prepared['primary_image'], $prepared['gallery_images']);

        $removeGalleryIds = collect((array) ($input['remove_gallery_image_ids'] ?? []))
            ->map(fn ($id) => (int) $id)->filter(fn (int $id): bool => $id > 0)->unique()->values()->all();

        $galleryPaths = [];
        foreach ((array) ($files['gallery_images'] ?? []) as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $galleryPaths[] = $this->uploads->store($file, 'uploads/products/gallery');
            }
        }

        $openingStock = $this->extractOpeningStock($prepared);
        $translations = $this->translations->extract($prepared);
        $prepared = $this->normalizeHomepageSection($prepared, $module);

        // `products.product_type` is a NOT NULL legacy column that mirrors the
        // selected product type's slug and drives storefront navigation. Leave
        // it untouched when no type is selected (or the type has no slug) so an
        // update keeps the current value and an insert falls back to the table
        // default, instead of failing with "Column 'product_type' cannot be null".
        $productTypeSlug = filled($prepared['product_type_id'] ?? null)
            ? ProductType::query()->whereKey($prepared['product_type_id'])->value('slug')
            : null;

        if (filled($productTypeSlug)) {
            $prepared['product_type'] = $productTypeSlug;
        } else {
            unset($prepared['product_type']);
        }

        foreach (['mrp', 'dealer_price', 'customer_price'] as $field) {
            if (blank($prepared[$field] ?? null)) {
                $prepared[$field] = 0;
            }
        }
        $prepared['sort_order'] = blank($prepared['sort_order'] ?? null) ? 0 : $prepared['sort_order'];
        $prepared['homepage_sort_order'] = blank($prepared['homepage_sort_order'] ?? null) ? 0 : $prepared['homepage_sort_order'];

        return new ProductSaveData(
            $prepared, $primaryImagePath, $galleryPaths, $removeGalleryIds,
            $openingStock, $translations, $variants, $media,
        );
    }

    private function extractOpeningStock(array &$data): ?array
    {
        $fieldMap = [
            'opening_stock_warehouse_id' => 'warehouse_id',
            'opening_stock_batch_no' => 'batch_no',
            'opening_stock_manufacturing_date' => 'manufacturing_date',
            'opening_stock_expiry_date' => 'expiry_date',
            'opening_stock_purchase_price' => 'purchase_price',
            'opening_stock_quantity' => 'quantity',
            'opening_stock_reserved_quantity' => 'reserved_quantity',
            'opening_stock_low_stock_alert' => 'low_stock_alert',
        ];

        $stockInput = Arr::only($data, array_keys($fieldMap));
        $data = Arr::except($data, array_keys($fieldMap));
        if (! collect($stockInput)->contains(fn ($value): bool => ! blank($value))) {
            return null;
        }

        $stock = [];
        foreach ($fieldMap as $inputKey => $stockKey) {
            $value = $stockInput[$inputKey] ?? null;
            if (in_array($stockKey, ['purchase_price', 'reserved_quantity', 'low_stock_alert'], true) && blank($value)) {
                $value = 0;
            }
            $stock[$stockKey] = blank($value) ? null : $value;
        }

        return $stock;
    }

    private function normalizeHomepageSection(array $data, array $module): array
    {
        $conditionalFields = collect($module['fields'] ?? [])
            ->filter(fn (array $field): bool => ($field['visibility_field'] ?? null) === 'homepage_section_id' && ! empty($field['name']))
            ->keyBy('name');
        if ($conditionalFields->isEmpty()) {
            return $data;
        }

        $section = filled($data['homepage_section_id'] ?? null)
            ? ProductHomepageSection::query()->find($data['homepage_section_id'])
            : null;
        $sectionType = (string) ($section?->section_type ?? '');
        $layoutType = (string) ($section?->layout_type ?? '');

        foreach ($conditionalFields as $fieldName => $field) {
            $sectionTypes = array_values(array_filter((array) ($field['show_for_section_types'] ?? [])));
            $layoutTypes = array_values(array_filter((array) ($field['show_for_layout_types'] ?? [])));
            $keep = $section !== null;
            if ($keep && $sectionTypes !== []) {
                $keep = in_array($sectionType, $sectionTypes, true);
            }
            if ($keep && $layoutTypes !== []) {
                $keep = in_array($layoutType, $layoutTypes, true);
            }
            if (! $keep) {
                $data[$fieldName] = null;
            }
        }

        return $data;
    }
}
