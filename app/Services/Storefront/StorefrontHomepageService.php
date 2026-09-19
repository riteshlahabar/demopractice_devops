<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Contracts\Storefront\StorefrontHomepageContract;
use App\Models\Catalog\ProductHomepageSection;
use Illuminate\Support\Collection;

final class StorefrontHomepageService implements StorefrontHomepageContract
{
    public function __construct(
        private readonly StorefrontHomepageRepositoryContract $homepage
    ) {}

    public function content(string $audience): array
    {
        $homepageSections = $this->homepage->homepageSections();

        if ($homepageSections->isNotEmpty()) {
            return [
                'homepageRows' => $this->homepageRows($homepageSections, $audience),
                'homepageSettings' => $homepageSections,
                'banners' => $this->homepageSettingBanners($homepageSections),
                'sections' => $homepageSections->keyBy('section_key'),
                'productSections' => $this->homeProductSections($homepageSections, $audience),
                'services' => $this->homepageSettingItems($homepageSections, 'service_section'),
                'topSellingProducts' => $this->homepage->topSellingProducts($audience),
                'dealTimerProduct' => $this->homepage->dealTimerProduct($audience),
                'footerLinks' => $this->homepage->footerLinks(),
            ];
        }

        $sections = $this->homepage->legacySections();

        return [
            'homepageRows' => collect(),
            'homepageSettings' => collect(),
            'banners' => $this->homepage->legacyBanners(),
            'sections' => $sections->keyBy('section_key'),
            'productSections' => $this->homeProductSections($sections, $audience),
            'services' => $this->homepage->serviceBlocks(),
            'topSellingProducts' => $this->homepage->topSellingProducts($audience),
            'dealTimerProduct' => $this->homepage->dealTimerProduct($audience),
            'footerLinks' => $this->homepage->footerLinks(),
        ];
    }

    public function emptyContent(): array
    {
        return [
            'homepageRows' => collect(),
            'homepageSettings' => collect(),
            'banners' => collect(),
            'sections' => collect(),
            'productSections' => collect(),
            'services' => collect(),
            'topSellingProducts' => collect(),
            'dealTimerProduct' => null,
            'footerLinks' => collect(),
        ];
    }

    private function homeProductSections(Collection $sections, string $audience): Collection
    {
        return $sections
            ->filter(
                fn ($section): bool => in_array(
                    (string) $section->section_type,
                    ['product', 'product_section', 'top_selling_section'],
                    true
                ) || (string) ($section->source_type ?? '') === 'top_selling_products'
            )
            ->map(function ($section) use ($audience): array {
                $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));
                $products = $section instanceof ProductHomepageSection
                    ? $this->homepage->productsForHomepageSection($section, $limit, $audience)
                    : $this->homepage->productsForLegacySection($section, $limit, $audience);

                return ['section' => $section, 'products' => $products];
            })
            ->filter(fn (array $entry): bool => $entry['products']->isNotEmpty())
            ->values();
    }

    private function homepageRows(Collection $sections, string $audience): Collection
    {
        return $sections
            ->map(function (ProductHomepageSection $section) use ($audience): array {
                $limit = max(1, min(50, (int) ($section->product_limit ?: 8)));

                return [
                    'section' => $section,
                    'items' => $section->items
                        ->where('is_active', true)
                        ->sortBy('sort_order')
                        ->values(),
                    'products' => $this->homepage
                        ->productsForHomepageSection($section, $limit, $audience),
                ];
            })
            ->values();
    }

    private function homepageSettingItems(Collection $sections, string $sectionType): Collection
    {
        return $sections
            ->where('section_type', $sectionType)
            ->flatMap(fn (ProductHomepageSection $section) => $section->items->where('is_active', true))
            ->sortBy('sort_order')
            ->values();
    }

    private function homepageSettingBanners(Collection $sections): Collection
    {
        $groups = collect();

        foreach ($sections as $section) {
            $placement = match ($section->section_type) {
                'hero_slider' => 'hero_main',
                'top_small_banners' => 'promo_small',
                'coupon_section' => 'bank_offer',
                'strip_offer_banner' => 'strip_banner',
                'blog_section' => 'blog',
                'offer_section' => $section->layout_type === 'big_small_banner'
                    ? 'footer_promo'
                    : 'middle_promo',
                default => $section->section_key,
            };

            $items = $section->items
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->values();

            if ($items->isEmpty()) {
                continue;
            }

            $groups->put($placement, ($groups->get($placement, collect()))->merge($items));
        }

        return $groups;
    }
}
