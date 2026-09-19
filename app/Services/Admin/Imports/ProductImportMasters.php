<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportRowReaderContract;
use App\Contracts\Admin\Imports\ProductImportMastersContract;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\ProductType;
use App\Models\Catalog\Unit;
use Illuminate\Support\Str;

final class ProductImportMasters implements ProductImportMastersContract
{
    public function __construct(private readonly ImportRowReaderContract $reader) {}

    public function ensure(array $row): void
    {
        $this->ensureCategory($this->reader->firstFilled($row, ['category_name', 'category']));
        $this->ensureBrand($this->reader->firstFilled($row, ['brand_name', 'brand']));
        $this->ensureProductType($this->reader->firstFilled($row, ['product_type', 'product_type_name']));
        $this->ensureUnit($this->reader->firstFilled($row, ['unit_short_name', 'unit', 'unit_name']));
    }

    private function ensureCategory(?string $name): void
    {
        if (blank($name)) {
            return;
        }

        Category::query()->firstOrCreate(['name' => $name], [
            'slug' => $this->uniqueSlug(Category::class, $name),
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function ensureBrand(?string $name): void
    {
        if (blank($name)) {
            return;
        }

        Brand::query()->firstOrCreate(['name' => $name], ['is_active' => true]);
    }

    private function ensureProductType(?string $name): void
    {
        if (blank($name)) {
            return;
        }

        ProductType::query()->firstOrCreate(['name' => $name], [
            'slug' => $this->uniqueSlug(ProductType::class, $name),
            'description' => $name,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    private function ensureUnit(?string $shortName): void
    {
        if (blank($shortName)) {
            return;
        }

        Unit::query()->firstOrCreate(['short_name' => $shortName], [
            'name' => strtoupper($shortName),
            'unit_type' => 'other',
            'decimal_precision' => 0,
            'is_active' => true,
        ]);
    }

    private function uniqueSlug(string $modelClass, string $name): string
    {
        $base = Str::slug($name) ?: 'import-item';
        $slug = $base;
        $counter = 1;

        while ($modelClass::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
