<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Contracts\Storefront\StorefrontNavigationContract;
use App\Models\Catalog\Product;
use Illuminate\Support\Collection;

final class StorefrontNavigationService implements StorefrontNavigationContract
{
    /** The Fastkart "Deal Today" popup is laid out for four products. */
    private const DEAL_LIMIT = 4;

    public function __construct(
        private readonly StorefrontNavigationRepositoryContract $navigation
    ) {}

    public function data(string $audience): array
    {
        $labels = $this->productTypeLabels();
        $productTypes = $this->navigation
            ->productTypeCounts($audience)
            ->map(fn (Product $product): array => [
                'slug' => $product->product_type,
                'name' => $labels[$product->product_type] ?? $this->formatProductType($product->product_type),
                'products_count' => (int) $product->products_count,
            ]);

        if ($productTypes->isEmpty()) {
            $productTypes = $this->emptyProductTypes($labels);
        }

        $featuredProducts = $this->navigation->featuredProducts($audience);
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $this->navigation->fallbackProducts($audience);
        }

        return [
            'categories' => $this->navigation->categories($audience),
            'categoryMenu' => $this->navigation->categoryMenu($audience, 12, 8),
            'productTypes' => $productTypes,
            'productTypeLabels' => $labels,
            'featuredProducts' => $featuredProducts,
            'topbarMessages' => $this->navigation->topbarMessages(),
            'dealProducts' => $this->navigation->dealProducts($audience, self::DEAL_LIMIT),
        ];
    }

    public function emptyData(): array
    {
        $labels = $this->productTypeLabels();

        return [
            'categories' => collect(),
            'categoryMenu' => collect(),
            'productTypes' => $this->emptyProductTypes($labels),
            'productTypeLabels' => $labels,
            'featuredProducts' => collect(),
            'topbarMessages' => collect(),
            'dealProducts' => collect(),
        ];
    }

    private function emptyProductTypes(array $labels): Collection
    {
        return collect($labels)
            ->map(fn (string $name, string $slug): array => [
                'slug' => $slug,
                'name' => $name,
                'products_count' => 0,
            ])
            ->values();
    }

    private function productTypeLabels(): array
    {
        return config('storefront.product_type_labels', [
            'medicine' => 'Medicine',
            'fertilizer' => 'Fertilizer',
            'seed' => 'Seeds',
            'seeds' => 'Seeds',
            'veterinary' => 'Veterinary Products',
            'veterinary_products' => 'Veterinary Products',
            'equipment' => 'Equipment',
            'other' => 'Other',
        ]);
    }

    private function formatProductType(string $productType): string
    {
        return str($productType)->replace(['_', '-'], ' ')->headline()->toString();
    }
}
