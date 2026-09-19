<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportSampleContract;

final class ImportSampleBuilder implements ImportSampleContract
{
    /**
     * Columns that are not module fields but which the import understands, so
     * the sample shows people they can use readable names instead of ids.
     */
    private const EXTRA_HEADERS = [
        'products' => ['product_type', 'category_name', 'brand_name', 'unit_short_name', 'gallery_images'],
        'categories' => ['parent_name'],
        'inventory' => ['product_sku', 'warehouse_code'],
        'batches' => ['product_sku', 'warehouse_code'],
        'storefront-banners' => ['product_sku'],
        'storefront-sections' => ['category_name'],
        'storefront-section-products' => ['section_key', 'product_sku'],
    ];

    public function headers(string $module, array $moduleConfig): array
    {
        $fields = collect($moduleConfig['fields'] ?? [])->pluck('name')->filter()->values()->all();

        return array_values(array_unique(array_merge(self::EXTRA_HEADERS[$module] ?? [], $fields)));
    }

    public function row(string $module, array $headers): array
    {
        $examples = $this->examples();

        return array_map(fn (string $header): string => $examples[$header] ?? '', $headers);
    }

    /**
     * @return array<string, string>
     */
    private function examples(): array
    {
        return [
            'placement' => 'hero_main',
            'title' => 'Premium Pesticides & Fertilizers',
            'subtitle' => 'Farmer Special Offer',
            'description' => 'Better crop protection and growth',
            'button_text' => 'Shop Now',
            'button_url' => '',
            'product_sku' => 'PES001',
            'image_path' => 'uploads/storefront/banners/banner1.jpg',
            'sort_order' => '1',
            'is_active' => '1',

            'category_name' => 'Pesticides',
            'brand_name' => 'Bawasakar',
            'unit_short_name' => 'ltr',
            'sku' => 'PES001',
            'name' => 'Premium Pesticide',
            'product_type' => 'fertilizer',
            'hsn_code' => '3808',
            'gst_percent' => '18',
            'mrp' => '500',
            'dealer_price' => '420',
            'customer_price' => '480',
            'primary_image' => 'calcium-main.jpg',
            'gallery_images' => 'calcium-1.jpg|calcium-2.jpg|calcium-3.jpg',
            'is_featured' => '1',
            'is_visible_to_dealers' => '1',
            'is_visible_to_customers' => '1',

            'short_name' => 'ltr',
            'unit_type' => 'volume',
            'decimal_precision' => '2',

            'warehouse_code' => 'WH001',
            'code' => 'WH001',
            'batch_no' => 'BATCH001',
            'quantity' => '100',
            'reserved_quantity' => '0',
            'low_stock_alert' => '10',
            'expiry_date' => date('Y-m-d', strtotime('+1 year')),

            'section_key' => 'featured_products',
            'section_type' => 'product',
            'source_type' => 'manual',
            'product_limit' => '10',
        ];
    }
}
