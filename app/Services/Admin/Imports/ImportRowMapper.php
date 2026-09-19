<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportImagePathContract;
use App\Contracts\Admin\Imports\ImportRowMapperContract;
use App\Contracts\Admin\Imports\ImportRowReaderContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class ImportRowMapper implements ImportRowMapperContract
{
    private const BOOLEAN_FIELDS = ['is_active', 'is_featured', 'is_visible_to_customers', 'is_visible_to_dealers'];

    private const INTEGER_FIELDS = ['sort_order', 'decimal_precision', 'product_limit'];

    private const DECIMAL_FIELDS = [
        'gst_percent', 'mrp', 'customer_price', 'dealer_price',
        'purchase_price', 'quantity', 'reserved_quantity', 'low_stock_alert',
    ];

    private const IMAGE_FIELDS = ['image_path', 'primary_image', 'product_image', 'icon_path'];

    public function __construct(
        private readonly ImportRowReaderContract $reader,
        private readonly ImportImagePathContract $paths,
    ) {}

    public function map(array $row, array $fields, string $module): array
    {
        $data = [];

        foreach ($fields as $field) {
            $key = $this->reader->header($field);

            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);

            if ($value === '') {
                continue;
            }

            $data[$field] = $this->cast($field, $value);
        }

        foreach (self::IMAGE_FIELDS as $imageField) {
            if (! empty($data[$imageField])) {
                $data[$imageField] = $this->paths->normalize((string) $data[$imageField], $module);
            }
        }

        if (empty($data['slug']) && ! empty($data['name']) && $module === 'categories') {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }

    public function uniqueKeysFor(array $data, string $module): array
    {
        $filled = static fn (array $keys): array => array_filter(
            $keys,
            static fn ($value) => $value !== null && $value !== '',
        );

        return match ($module) {
            'storefront-banners' => $filled([
                'placement' => $data['placement'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
            ]),

            'storefront-sections' => ! empty($data['section_key']) ? ['section_key' => $data['section_key']] : [],

            'storefront-section-products' => array_filter([
                'section_id' => $data['section_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
            ]),

            'storefront-service-blocks' => $filled([
                'title' => $data['title'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
            ]),

            'storefront-footer-links' => $filled([
                'link_group' => $data['link_group'] ?? null,
                'title' => $data['title'] ?? null,
            ]),

            'categories' => $this->firstKey($data, ['slug', 'name']),
            'brands' => $this->firstKey($data, ['name']),
            'units' => $this->firstKey($data, ['short_name', 'name']),
            'products' => $this->firstKey($data, ['sku', 'name']),
            'warehouses' => $this->firstKey($data, ['code', 'name']),

            'batches', 'inventory' => array_filter([
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'batch_no' => $data['batch_no'] ?? null,
            ]),

            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $candidates
     * @return array<string, mixed>
     */
    private function firstKey(array $data, array $candidates): array
    {
        foreach ($candidates as $candidate) {
            if (! empty($data[$candidate])) {
                return [$candidate => $data[$candidate]];
            }
        }

        return [];
    }

    private function cast(string $field, mixed $value): mixed
    {
        $value = trim((string) $value);

        if (in_array($field, self::BOOLEAN_FIELDS, true)) {
            return in_array(strtolower($value), ['1', 'yes', 'true', 'active', 'on'], true);
        }

        if (str_contains($field, 'date')) {
            // Excel stores dates as days since its own epoch.
            return is_numeric($value)
                ? Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString()
                : Carbon::parse($value)->toDateString();
        }

        if (in_array($field, self::INTEGER_FIELDS, true)) {
            return (int) $value;
        }

        if (in_array($field, self::DECIMAL_FIELDS, true)) {
            return (float) $value;
        }

        return $value;
    }
}
