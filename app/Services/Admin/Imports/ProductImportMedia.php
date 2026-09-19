<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportImagePathContract;
use App\Contracts\Admin\Imports\ProductImportMediaContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontSectionProduct;

final class ProductImportMedia implements ProductImportMediaContract
{
    public function __construct(private readonly ImportImagePathContract $paths) {}

    public function syncPrimaryImage(Product $product, string $path): void
    {
        ProductImage::query()->updateOrCreate(
            ['product_id' => $product->id, 'is_primary' => true],
            [
                'path' => trim($this->paths->normalize($path, 'products'), '/'),
                'sort_order' => 1,
            ]
        );
    }

    public function syncGalleryImages(Product $product, array $paths): void
    {
        $startOrder = (int) ProductImage::query()
            ->where('product_id', $product->id)
            ->max('sort_order');

        foreach ($paths as $index => $path) {
            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'path' => trim($path, '/')],
                ['is_primary' => false, 'sort_order' => $startOrder + $index + 1]
            );
        }
    }

    public function syncHomepageDisplay(Product $product): void
    {
        $rows = config('homepage_rows.product_rows', []);
        $selectedRow = (string) ($product->storefront_row ?? '');
        $productUrl = route('store.product', ['product' => $product->getKey()], false);

        $this->clearExistingPlacements($product, $productUrl, $rows);

        if ($selectedRow === '' || ! isset($rows[$selectedRow])) {
            return;
        }

        $row = $rows[$selectedRow];

        match ($row['type'] ?? '') {
            'banner' => $this->placeBanner($product, $row, $productUrl),
            'product' => $this->placeInSection($product, $row),
            default => null,
        };
    }

    /**
     * A re-import must not leave the product sitting in a row it no longer
     * selects, so every previous placement goes first.
     *
     * @param  array<string, mixed>  $rows
     */
    private function clearExistingPlacements(Product $product, string $productUrl, array $rows): void
    {
        StorefrontBanner::query()->where('button_url', $productUrl)->delete();

        $sectionIds = StorefrontSection::query()
            ->whereIn('section_key', collect($rows)->where('type', 'product')->pluck('section_key')->filter()->values())
            ->pluck('id');

        if ($sectionIds->isNotEmpty()) {
            StorefrontSectionProduct::query()
                ->whereIn('section_id', $sectionIds)
                ->where('product_id', $product->getKey())
                ->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function placeBanner(Product $product, array $row, string $productUrl): void
    {
        $imagePath = $product->storefront_banner_image ?: optional($product->images->first())->path;

        if (! $imagePath) {
            return;
        }

        StorefrontBanner::query()->create([
            'placement' => $row['placement'],
            'title' => $product->storefront_title ?: $product->name,
            'subtitle' => $product->storefront_subtitle,
            'description' => $product->storefront_description ?: $product->description,
            'button_text' => 'Shop Now',
            'button_url' => $productUrl,
            'image_path' => $imagePath,
            'sort_order' => (int) ($product->sort_order ?? 0),
            'is_active' => (bool) $product->is_active,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function placeInSection(Product $product, array $row): void
    {
        $section = StorefrontSection::query()->updateOrCreate(
            ['section_key' => $row['section_key']],
            [
                'title' => $row['title'],
                'section_type' => 'product',
                'source_type' => 'manual',
                'category_id' => null,
                'product_limit' => 24,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => true,
            ]
        );

        StorefrontSectionProduct::query()->updateOrCreate(
            ['section_id' => $section->getKey(), 'product_id' => $product->getKey()],
            ['sort_order' => (int) ($product->sort_order ?? 0), 'is_active' => (bool) $product->is_active]
        );
    }
}
