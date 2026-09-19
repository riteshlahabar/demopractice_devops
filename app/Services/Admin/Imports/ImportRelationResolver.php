<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportRelationResolverContract;
use App\Contracts\Admin\Imports\ImportRowReaderContract;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductType;
use App\Models\Catalog\Unit;
use App\Models\Inventory\Warehouse;
use App\Models\Storefront\StorefrontSection;
use Illuminate\Database\Eloquent\Model;

final class ImportRelationResolver implements ImportRelationResolverContract
{
    public function __construct(private readonly ImportRowReaderContract $reader) {}

    public function resolve(array $data, array $row, string $module): array
    {
        if ($module === 'products' || $module === 'storefront-sections') {
            $data = $this->set($data, 'category_id', $this->findCategory($row));
        }

        if ($module === 'products') {
            $data = $this->set($data, 'brand_id', $this->byName(Brand::class, $this->reader->firstFilled($row, ['brand_name', 'brand'])));
            $data = $this->set($data, 'unit_id', $this->findUnit($row));
            $data = $this->set($data, 'product_type_id', $this->byName(ProductType::class, $this->reader->firstFilled($row, ['product_type', 'product_type_name'])));
        }

        if (in_array($module, ['batches', 'inventory'], true)) {
            $data = $this->set($data, 'warehouse_id', $this->findWarehouse($row));
        }

        if (in_array($module, ['batches', 'inventory', 'storefront-section-products'], true)) {
            $data = $this->set($data, 'product_id', $this->findProduct($row));
        }

        if ($module === 'storefront-section-products') {
            $data = $this->set($data, 'section_id', $this->findSection($row));
        }

        return $this->applyBannerProductLink($data, $row, $module);
    }

    /**
     * A banner row that names a product but no link gets one pointing at that
     * product's detail page.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function applyBannerProductLink(array $data, array $row, string $module): array
    {
        if ($module !== 'storefront-banners' || ! empty($data['button_url'])) {
            return $data;
        }

        $product = $this->findProduct($row);

        if ($product) {
            $data['button_url'] = route('store.product', ['product' => $product->id], false);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function set(array $data, string $key, ?Model $model): array
    {
        if ($model) {
            $data[$key] = $model->getKey();
        }

        return $data;
    }

    private function findProduct(array $row): ?Product
    {
        return $this->firstMatch(Product::query(), [
            'id' => $this->reader->firstFilled($row, ['product_id']),
            'sku' => $this->reader->firstFilled($row, ['product_sku', 'sku']),
            'name' => $this->reader->firstFilled($row, ['product_name', 'product']),
        ]);
    }

    private function findCategory(array $row): ?Category
    {
        return $this->firstMatch(Category::query(), [
            'id' => $this->reader->firstFilled($row, ['category_id']),
            'slug' => $this->reader->firstFilled($row, ['category_slug', 'slug']),
            'name' => $this->reader->firstFilled($row, ['category_name', 'category']),
        ]);
    }

    private function findUnit(array $row): ?Unit
    {
        return $this->firstMatch(Unit::query(), [
            'id' => $this->reader->firstFilled($row, ['unit_id']),
            'short_name' => $this->reader->firstFilled($row, ['unit_short_name', 'short_name', 'unit']),
            'name' => $this->reader->firstFilled($row, ['unit_name']),
        ]);
    }

    private function findWarehouse(array $row): ?Warehouse
    {
        return $this->firstMatch(Warehouse::query(), [
            'id' => $this->reader->firstFilled($row, ['warehouse_id']),
            'code' => $this->reader->firstFilled($row, ['warehouse_code', 'code']),
            'name' => $this->reader->firstFilled($row, ['warehouse_name', 'warehouse']),
        ]);
    }

    private function findSection(array $row): ?StorefrontSection
    {
        return $this->firstMatch(StorefrontSection::query(), [
            'id' => $this->reader->firstFilled($row, ['section_id']),
            'section_key' => $this->reader->firstFilled($row, ['section_key']),
            'title' => $this->reader->firstFilled($row, ['section_title', 'section']),
        ]);
    }

    /**
     * Looks the record up by the first candidate the row actually supplied, in
     * order of how specific it is.
     *
     * @param  array<string, string|null>  $candidates
     */
    private function firstMatch(mixed $query, array $candidates): ?Model
    {
        foreach ($candidates as $column => $value) {
            if (filled($value)) {
                return $query->where($column, $value)->first();
            }
        }

        return null;
    }

    private function byName(string $model, ?string $name): ?Model
    {
        return blank($name) ? null : $model::query()->where('name', $name)->first();
    }
}
