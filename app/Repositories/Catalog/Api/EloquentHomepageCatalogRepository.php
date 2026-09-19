<?php

namespace App\Repositories\Catalog\Api;

use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontBanner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentHomepageCatalogRepository implements HomepageCatalogRepositoryContract
{
    public function activeSections(): Collection
    {
        return ProductHomepageSection::query()
            ->with([
                'category.translations',
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

    public function activeCategories(): Collection
    {
        return Category::query()
            ->with('translations')
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN show_on_homepage = 1 THEN 0 ELSE 1 END')
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function productsForSection(
        ProductHomepageSection $section,
        int $limit,
        string $audience
    ): Collection {
        $assigned = $this->productQuery($audience)
            ->where('show_on_homepage', true)
            ->where('homepage_section_id', $section->id)
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($section->source_type === 'top_selling_products' || $section->section_type === 'top_selling_section') {
            $topSelling = $this->productQuery($audience)
                ->where('show_on_homepage', true)
                ->where('is_top_selling', true)
                ->orderBy('homepage_sort_order')
                ->orderBy('sort_order')
                ->latest('id')
                ->limit($limit)
                ->get();

            return $assigned->concat($topSelling)->unique('id')->values();
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
        } elseif ($section->source_type === 'deal_timer_product') {
            $query->where('is_deal_timer_product', true);
        } else {
            return collect();
        }

        return $query
            ->orderBy('homepage_sort_order')
            ->orderBy('sort_order')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function fallbackBanners(ProductHomepageSection $section): Collection
    {
        $placement = match ($section->section_type) {
            'hero_slider' => 'hero_main',
            'top_small_banners' => 'promo_small',
            'coupon_section' => 'bank_offer',
            default => null,
        };

        if (! $placement) {
            return collect();
        }

        return StorefrontBanner::query()
            ->where('is_active', true)
            ->where('placement', $placement)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function legacyBanners(): Collection
    {
        return StorefrontBanner::query()
            ->where('is_active', true)
            ->where('placement', 'hero_main')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    private function productQuery(string $audience): Builder
    {
        return Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images'])
            ->visibleFor($audience);
    }
}
