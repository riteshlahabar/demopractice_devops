<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Storefront\StorefrontSection;
use App\Services\Storefront\StorefrontHomepageService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class StorefrontHomepageServiceTest extends TestCase
{
    public function test_homepage_keeps_existing_cms_and_product_section_shape(): void
    {
        $section = new ProductHomepageSection([
            'section_key' => 'featured',
            'section_type' => 'product_section',
            'source_type' => 'featured_products',
            'product_limit' => 8,
        ]);
        $section->id = 10;
        $section->setRelation('items', collect());

        $product = new Product;
        $product->id = 20;

        $repository = new class($section, $product) implements StorefrontHomepageRepositoryContract
        {
            public function __construct(
                private readonly ProductHomepageSection $section,
                private readonly Product $product
            ) {}

            public function homepageSections(): Collection
            {
                return collect([$this->section]);
            }

            public function legacySections(): Collection
            {
                return collect();
            }

            public function legacyBanners(): Collection
            {
                return collect();
            }

            public function serviceBlocks(): Collection
            {
                return collect();
            }

            public function footerLinks(): Collection
            {
                return collect(['company' => collect()]);
            }

            public function productsForLegacySection(
                StorefrontSection $section,
                int $limit,
                string $audience
            ): Collection {
                return collect();
            }

            public function productsForHomepageSection(
                ProductHomepageSection $section,
                int $limit,
                string $audience
            ): Collection {
                return collect([$this->product]);
            }

            public function topSellingProducts(string $audience): Collection
            {
                return collect([$this->product]);
            }

            public function dealTimerProduct(string $audience): ?Product
            {
                return $this->product;
            }
        };

        $result = (new StorefrontHomepageService($repository))->content('customer');

        $this->assertSame($section, $result['homepageSettings']->first());
        $this->assertSame([20], $result['homepageRows']->first()['products']->pluck('id')->all());
        $this->assertSame([20], $result['productSections']->first()['products']->pluck('id')->all());
        $this->assertSame([20], $result['topSellingProducts']->pluck('id')->all());
        $this->assertSame($product, $result['dealTimerProduct']);
    }
}
