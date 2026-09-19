<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontBanner;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontSection;
use App\Models\Storefront\StorefrontServiceBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentStorefrontHomepageRepository implements StorefrontHomepageRepositoryContract
{
    public function homepageSections(): Collection
    {
        return ProductHomepageSection::query()
            ->with([
                'category',
                'items' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function legacySections(): Collection
    {
        return StorefrontSection::query()
            ->with([
                'category',
                'sectionProducts.product.images',
                'sectionProducts.product.category',
                'sectionProducts.product.brand',
                'sectionProducts.product.unit',
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function legacyBanners(): Collection
    {
        return StorefrontBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->groupBy('placement');
    }

    public function serviceBlocks(): Collection
    {
        return StorefrontServiceBlock::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function footerLinks(): Collection
    {
        return StorefrontFooterLink::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('link_group');
    }

    public function productsForLegacySection(
        StorefrontSection $section,
        int $limit,
        string $audience
    ): Collection {
        if ($section->source_type === 'manual') {
            $visibleColumn = $audience === 'dealer'
                ? 'is_visible_to_dealers'
                : 'is_visible_to_customers';

            return $section->sectionProducts
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->pluck('product')
                ->filter(
                    fn (?Product $product): bool => $product
                        && $product->is_active
                        && (bool) $product->{$visibleColumn}
                )
                ->take($limit)
                ->values();
        }

        $query = $this->productQuery($audience);

        if ($section->source_type === 'category' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        }

        if ($section->source_type === 'featured') {
            $query->where('is_featured', true);
        }

        return $query->storefrontOrder()->limit($limit)->get();
    }

    public function productsForHomepageSection(
        ProductHomepageSection $section,
        int $limit,
        string $audience
    ): Collection {
        $limit = max(1, $limit ?: (int) ($section->product_limit ?: 8));

        $assigned = $this->productQuery($audience)
            ->where('show_on_homepage', true)
            ->where('homepage_section_id', $section->id)
            ->orderBy('homepage_sort_order')
            ->storefrontOrder()
            ->limit($limit)
            ->get();

        if ($section->source_type === 'top_selling_products' || $section->section_type === 'top_selling_section') {
            $topSellingProducts = $this->productQuery($audience)
                ->where('show_on_homepage', true)
                ->where('is_top_selling', true)
                ->orderBy('homepage_sort_order')
                ->storefrontOrder()
                ->limit($limit)
                ->get();

            return $assigned
                ->concat($topSellingProducts)
                ->unique('id')
                ->values();
        }

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        $query = $this->productQuery($audience)->where('show_on_homepage', true);

        if ($section->source_type === 'category_products' && $section->category_id) {
            $query->where('category_id', $section->category_id);
        } elseif ($section->source_type === 'featured_products') {
            $query->where('is_featured', true);
        } elseif ($section->source_type === 'latest_products') {
            $query->latest('id');
        } elseif ($section->source_type === 'offer_products') {
            $query->where('is_offer_product', true);
        } elseif ($section->source_type === 'top_selling_products' || $section->section_type === 'top_selling_section') {
            $query->where('is_top_selling', true);
        } elseif ($section->source_type === 'deal_timer_product') {
            $query->where('is_deal_timer_product', true);
        } else {
            return collect();
        }

        return $query
            ->orderBy('homepage_sort_order')
            ->storefrontOrder()
            ->limit($limit)
            ->get();
    }

    public function topSellingProducts(string $audience): Collection
    {
        return $this->productQuery($audience)
            ->where('show_on_homepage', true)
            ->where('is_top_selling', true)
            ->storefrontOrder()
            ->limit(8)
            ->get();
    }

    public function dealTimerProduct(string $audience): ?Product
    {
        return $this->productQuery($audience)
            ->where('show_on_homepage', true)
            ->where('is_deal_timer_product', true)
            ->storefrontOrder()
            ->first();
    }

    private function productQuery(string $audience): Builder
    {
        return Product::query()
            ->with([
                'category.translations',
                'brand',
                'unit',
                'images',
                'translations',
                'inventoryBatches',
                'variants.inventoryBatches',
            ])
            ->visibleFor($audience);
    }
}
