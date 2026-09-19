<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Storefront\StorefrontBanner;
use App\Services\Catalog\Api\HomepageCatalogService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class HomepageCatalogServiceTest extends TestCase
{
    public function test_service_builds_existing_homepage_shape_from_replaceable_dependencies(): void
    {
        $result = $this->homepageFor('product_section');

        $this->assertSame([['id' => 12]], $result['categories']->all());
        $this->assertSame('featured', $result['rows']->first()['section_key']);
        $this->assertSame('homepage_section:Featured', $result['rows']->first()['title'], 'Section titles go through the website translation.');
        $this->assertSame([['id' => 11]], $result['rows']->first()['items']->all());
        $this->assertSame([['id' => 13]], $result['rows']->first()['products']->all());
        $this->assertSame([], $result['banners']->all());
    }

    public function test_banner_sections_use_assigned_products_as_entries_like_the_storefront(): void
    {
        foreach (['top_small_banners', 'offer_section'] as $type) {
            $row = $this->homepageFor($type)['rows']->first();

            $this->assertSame([['entry' => 13, 'no_name' => true]], $row['items']->all(), "{$type} should show the product entry without the product name.");
            $this->assertSame([], $row['products']->all(), "{$type} should not also send product cards.");
        }
    }

    public function test_bank_offers_keep_the_product_name_as_offer_title(): void
    {
        $row = $this->homepageFor('coupon_section')['rows']->first();

        $this->assertSame([['entry' => 13]], $row['items']->all());
        $this->assertSame([], $row['products']->all());
    }

    public function test_hero_banners_come_from_assigned_products_without_the_product_name(): void
    {
        $this->assertSame([['entry' => 13, 'no_name' => true]], $this->homepageFor('hero_slider')['banners']->all());
    }

    public function test_banner_sections_fall_back_to_items_without_products(): void
    {
        $row = $this->homepageFor('coupon_section', withProduct: false)['rows']->first();

        $this->assertSame([['id' => 11]], $row['items']->all());
    }

    private function homepageFor(string $sectionType, bool $withProduct = true): array
    {
        $section = new ProductHomepageSection([
            'section_key' => 'featured',
            'title' => 'Featured',
            'subtitle' => 'Selected products',
            'section_type' => $sectionType,
            'layout_type' => 'grid',
            'source_type' => 'featured_products',
            'sort_order' => 2,
            'product_limit' => 8,
        ]);
        $section->id = 10;

        $item = new ProductHomepageSectionItem(['is_active' => true, 'sort_order' => 1]);
        $item->id = 11;
        $section->setRelation('items', collect([$item]));

        $category = new Category;
        $category->id = 12;

        $product = new Product;
        $product->id = 13;

        $repository = new class($section, $category, $withProduct ? collect([$product]) : collect()) implements HomepageCatalogRepositoryContract
        {
            public function __construct(
                private readonly ProductHomepageSection $section,
                private readonly Category $category,
                private readonly Collection $products
            ) {}

            public function activeSections(): Collection
            {
                return collect([$this->section]);
            }

            public function activeCategories(): Collection
            {
                return collect([$this->category]);
            }

            public function productsForSection(
                ProductHomepageSection $section,
                int $limit,
                string $audience
            ): Collection {
                return $this->products;
            }

            public function fallbackBanners(ProductHomepageSection $section): Collection
            {
                return collect();
            }

            public function legacyBanners(): Collection
            {
                return collect();
            }
        };

        $categoryPresenter = new class implements CategoryCatalogPresenterContract
        {
            public function present(Category $category): array
            {
                return ['id' => $category->id];
            }
        };

        $productPresenter = new class implements ProductCatalogPresenterContract
        {
            public function present(Product $product): array
            {
                return ['id' => $product->id];
            }
        };

        $homepagePresenter = new class implements HomepageCatalogPresenterContract
        {
            public function item(ProductHomepageSectionItem $item): array
            {
                return ['id' => $item->id];
            }

            public function productEntry(Product $product, bool $nameAsTitle = true): array
            {
                return $nameAsTitle ? ['entry' => $product->id] : ['entry' => $product->id, 'no_name' => true];
            }

            public function fallbackBanner(StorefrontBanner $banner): array
            {
                return ['id' => $banner->id];
            }

            public function legacyBanner(StorefrontBanner $banner): array
            {
                return ['id' => $banner->id];
            }
        };

        return (new HomepageCatalogService(
            $repository,
            $categoryPresenter,
            $productPresenter,
            $homepagePresenter,
            new class implements CatalogTextTranslatorContract
            {
                public function text(?string $text, string $group): ?string
                {
                    return $text === null ? null : $group.':'.$text;
                }
            }
        ))->homepage('customer');
    }
}
