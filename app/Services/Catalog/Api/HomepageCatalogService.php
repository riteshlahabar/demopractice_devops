<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
use App\Contracts\Catalog\Api\HomepageCatalogContract;
use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Support\Collection;

final class HomepageCatalogService implements HomepageCatalogContract
{
    /**
     * Sections the storefront template builds from "products first, section
     * items only when no product is assigned".
     */
    private const ENTRY_SECTIONS = ['hero_slider', 'top_small_banners', 'coupon_section', 'offer_section'];

    public function __construct(
        private readonly HomepageCatalogRepositoryContract $homepage,
        private readonly CategoryCatalogPresenterContract $categories,
        private readonly ProductCatalogPresenterContract $products,
        private readonly HomepageCatalogPresenterContract $presenter,
        private readonly CatalogTextTranslatorContract $translator
    ) {}

    public function homepage(string $audience): array
    {
        $sections = $this->homepage->activeSections();
        $categories = $this->homepage->activeCategories()
            ->map(fn (Category $category): array => $this->categories->present($category))
            ->values();

        if ($sections->isEmpty()) {
            return [
                'banners' => $this->homepage->legacyBanners()
                    ->map(fn (StorefrontBanner $banner): array => $this->presenter->legacyBanner($banner))
                    ->values(),
                'categories' => $categories,
                'rows' => [],
            ];
        }

        $rows = $sections
            ->map(function (ProductHomepageSection $section) use ($audience): array {
                $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));
                $products = $this->homepage->productsForSection($section, $limit, $audience);

                return [
                    'section_key' => $section->section_key,
                    'title' => $this->translator->text($section->title, 'homepage_section'),
                    'subtitle' => $this->translator->text($section->subtitle, 'homepage_section'),
                    'section_type' => $section->section_type,
                    'layout_type' => $section->layout_type,
                    'source_type' => $section->source_type,
                    'sort_order' => $section->sort_order,
                    ...$this->content($section, $products),
                ];
            })
            ->values();

        $heroBanners = $rows
            ->where('section_type', 'hero_slider')
            ->flatMap(fn (array $row) => $row['items'])
            ->values();

        return [
            'banners' => $heroBanners,
            'categories' => $categories,
            'rows' => $rows,
        ];
    }

    /**
     * Banner / offer sections send their products as entries in `items` (and
     * no product cards), falling back to the section's own items only when no
     * product is assigned — the same rule as the storefront template, so the
     * apps never show older banners than the website.
     *
     * @return array{items: Collection, products: Collection}
     */
    private function content(ProductHomepageSection $section, Collection $products): array
    {
        $type = (string) $section->section_type;

        if (in_array($type, self::ENTRY_SECTIONS, true)) {
            // Banners never show the product name on top of the image; bank
            // offer cards still use it as the offer name.
            $nameAsTitle = $type === 'coupon_section';

            return [
                'items' => $products->isNotEmpty() ? $this->productEntries($products, $nameAsTitle) : $this->sectionItems($section),
                'products' => collect(),
            ];
        }

        if ($type === 'strip_offer_banner') {
            return [
                'items' => $this->productEntries($products, false)->concat($this->sectionItems($section))->values(),
                'products' => collect(),
            ];
        }

        return [
            'items' => $this->sectionItems($section),
            'products' => $products
                ->map(fn (Product $product): array => $this->products->present($product))
                ->values(),
        ];
    }

    private function productEntries(Collection $products, bool $nameAsTitle): Collection
    {
        return $products
            ->map(fn (Product $product): array => $this->presenter->productEntry($product, $nameAsTitle))
            ->values();
    }

    private function sectionItems(ProductHomepageSection $section): Collection
    {
        $items = $section->items
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->map(fn (ProductHomepageSectionItem $item): array => $this->presenter->item($item))
            ->values();

        if ($items->isNotEmpty()) {
            return $items;
        }

        return $this->homepage->fallbackBanners($section)
            ->map(fn (StorefrontBanner $banner): array => $this->presenter->fallbackBanner($banner))
            ->values();
    }
}
