<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductValidationContract;
use App\Models\Catalog\Product;
use App\Rules\Catalog\SingleMainVariant;

final class ProductValidationService implements ProductValidationContract
{
    public function extend(array $rules, ?Product $product = null): array
    {
        $rules = array_merge($rules, [
            'variants' => ['required', 'array', 'min:1', new SingleMainVariant],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'variants.*.size_value' => ['required', 'numeric', 'min:0.001'],
            'variants.*.variant_sku' => ['nullable', 'string', 'max:100', 'distinct'],
            'variants.*.hsn_code' => ['nullable', 'string', 'max:40'],
            'variants.*.gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'variants.*.units_per_case' => ['required', 'integer', 'min:1'],
            'variants.*.mrp' => ['required', 'numeric', 'min:0'],
            'variants.*.dealer_price' => ['required', 'numeric', 'min:0'],
            'variants.*.customer_price' => ['required', 'numeric', 'min:0'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*.warehouse_id' => ['nullable', 'required_with:variants.*.opening_stock_quantity', 'exists:warehouses,id'],
            'variants.*.batch_no' => ['nullable', 'required_with:variants.*.opening_stock_quantity', 'string', 'max:80'],
            'variants.*.manufacturing_date' => ['nullable', 'date'],
            'variants.*.expiry_date' => ['nullable', 'date'],
            'variants.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.opening_stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'variants.*.low_stock_alert' => ['nullable', 'numeric', 'min:0'],
            'additional_info' => ['nullable', 'array'],
            'additional_info.*.label' => ['nullable', 'string', 'max:120'],
            'additional_info.*.value' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array'],
            'media.*.id' => ['nullable', 'integer', 'exists:product_media,id'],
            'media.*.source_type' => ['required', 'in:upload,youtube'],
            'media.*.file' => ['nullable', 'file', 'mimes:mp4,webm', 'max:51200'],
            'media.*.youtube_url' => ['nullable', 'url', 'max:2048'],
            'media.*.title' => ['nullable', 'string', 'max:255'],
            'media.*.thumbnail' => ['nullable', 'image', 'max:5120'],
            'media.*.existing_file_path' => ['nullable', 'string', 'max:2048'],
            'media.*.existing_thumbnail_path' => ['nullable', 'string', 'max:2048'],
            'media.*.language' => ['nullable', 'string', 'max:10'],
            'media.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'media.*.is_active' => ['nullable', 'boolean'],
        ]);

        if ($product) {
            return $rules;
        }

        $stockSupportFields = [
            'opening_stock_warehouse_id', 'opening_stock_batch_no',
            'opening_stock_manufacturing_date', 'opening_stock_expiry_date',
            'opening_stock_purchase_price', 'opening_stock_quantity',
            'opening_stock_reserved_quantity', 'opening_stock_low_stock_alert',
        ];

        $rules['opening_stock_warehouse_id'][] = 'required_with:'.implode(',', array_diff($stockSupportFields, ['opening_stock_warehouse_id']));
        $rules['opening_stock_batch_no'][] = 'required_with:'.implode(',', array_diff($stockSupportFields, ['opening_stock_batch_no']));
        $rules['opening_stock_quantity'][] = 'required_with:'.implode(',', array_diff($stockSupportFields, ['opening_stock_quantity']));
        $rules['opening_stock_expiry_date'][] = 'after_or_equal:opening_stock_manufacturing_date';
        $rules['opening_stock_reserved_quantity'][] = 'lte:opening_stock_quantity';

        return $rules;
    }
}
