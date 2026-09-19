<?php

namespace Database\Seeders;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Catalog\ProductImage;
use App\Models\Catalog\ProductType;
use App\Models\Catalog\Unit;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IndexFiveHomepageSeeder extends Seeder
{
    private const IMAGE_BASE = 'uploads/storefront/index-5';

    public function run(): void
    {
        $brand = Brand::query()->updateOrCreate(
            ['name' => 'Bawaskar Technology'],
            ['is_active' => true]
        );

        $unit = Unit::query()->updateOrCreate(
            ['short_name' => 'pcs'],
            [
                'name' => 'Pieces',
                'unit_type' => 'qty',
                'decimal_precision' => 0,
                'is_active' => true,
            ]
        );

        $productType = ProductType::query()->updateOrCreate(
            ['slug' => 'agri-inputs'],
            [
                'name' => 'Agri Inputs',
                'description' => 'Homepage demo products for index-5 storefront layout.',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $warehouse = Warehouse::query()->updateOrCreate(
            ['code' => 'WH-PUNE'],
            ['name' => 'Pune Main Warehouse', 'city' => 'Pune', 'is_active' => true]
        );

        $categories = $this->seedCategories();
        $sections = $this->seedSections($categories);

        $this->seedProducts($categories, $sections, $brand->id, $unit->id, $productType->id);
        $this->seedOfferItems($sections['offer']);
        $this->seedStripOfferItem($sections['strip'], $categories['seeds']);
        $this->seedServiceItems($sections['service']);
        $this->ensurePositiveStockForAllProducts($warehouse->id);
    }

    /**
     * Public upload folders used by this seeder:
     * - public/uploads/storefront/index-5/categories
     * - public/uploads/storefront/index-5/products
     * - public/uploads/storefront/index-5/sections/offers
     */
    private function seedCategories(): array
    {
        $definitions = [
            'veterinary-medicine' => [
                'name' => 'Veterinary Medicine',
                'sort_order' => 7,
                'homepage_product_limit' => 6,
                'homepage_sort_order' => 7,
                'image_path' => self::IMAGE_BASE.'/categories/veterinary-medicine.png',
            ],
            'animal-healthcare' => [
                'name' => 'Animal Healthcare',
                'sort_order' => 9,
                'homepage_product_limit' => 10,
                'homepage_sort_order' => 9,
                'image_path' => self::IMAGE_BASE.'/categories/animal-healthcare.png',
            ],
            'seeds' => [
                'name' => 'Seeds',
                'sort_order' => 12,
                'homepage_product_limit' => 8,
                'homepage_sort_order' => 12,
                'image_path' => self::IMAGE_BASE.'/categories/seeds.png',
            ],
        ];

        $categories = [];

        foreach ($definitions as $slug => $definition) {
            $categories[$slug] = Category::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'is_active' => true,
                    'sort_order' => $definition['sort_order'],
                    'show_on_homepage' => true,
                    'homepage_title' => $definition['name'],
                    'homepage_layout' => 'slider',
                    'homepage_product_limit' => $definition['homepage_product_limit'],
                    'homepage_sort_order' => $definition['homepage_sort_order'],
                    'image_path' => $definition['image_path'],
                ]
            );
        }

        return $categories;
    }

    private function seedSections(array $categories): array
    {
        $lastProductSectionSortOrder = (int) ProductHomepageSection::query()
            ->where('section_type', 'product_section')
            ->max('sort_order');

        $topSelling = $this->upsertSection(
            ['section_type' => 'top_selling_section', 'title' => 'Top Selling Items'],
            [
                'section_key' => 'top-selling-items',
                'title' => 'Top Selling Items',
                'subtitle' => 'Fast moving products',
                'section_type' => 'top_selling_section',
                'layout_type' => 'product_grid',
                'source_type' => 'top_selling_products',
                'category_id' => null,
                'product_limit' => 10,
                'item_limit' => 0,
                'sort_order' => 5,
                'is_active' => true,
            ]
        );

        $offer = $this->upsertSection(
            ['section_key' => 'index5-offer-above-animal-healthcare'],
            [
                'section_key' => 'index5-offer-above-animal-healthcare',
                'title' => 'Offer Zone',
                'subtitle' => 'Featured seasonal banners',
                'section_type' => 'offer_section',
                'layout_type' => 'two_column_banner',
                'source_type' => 'banners',
                'category_id' => null,
                'product_limit' => 0,
                'item_limit' => 2,
                'sort_order' => 8,
                'is_active' => true,
            ]
        );

        $animalHealthcare = $this->upsertSection(
            ['section_type' => 'product_section', 'title' => 'Animal Healthcare'],
            [
                'section_key' => 'animal-healthcare-category-'.$categories['animal-healthcare']->id,
                'title' => 'Animal Healthcare',
                'section_type' => 'product_section',
                'layout_type' => 'product_slider',
                'source_type' => 'category_products',
                'category_id' => $categories['animal-healthcare']->id,
                'product_limit' => 10,
                'item_limit' => 0,
                'sort_order' => 9,
                'is_active' => true,
            ]
        );

        $strip = $this->upsertSection(
            ['section_key' => 'index5-strip-offer-above-seeds'],
            [
                'section_key' => 'index5-strip-offer-above-seeds',
                'title' => 'Seed Savings Strip',
                'subtitle' => 'Single strip offer banner',
                'section_type' => 'strip_offer_banner',
                'layout_type' => 'text_banner',
                'source_type' => 'text',
                'category_id' => null,
                'product_limit' => 0,
                'item_limit' => 1,
                'sort_order' => 11,
                'is_active' => true,
            ]
        );

        $seeds = $this->upsertSection(
            ['section_type' => 'product_section', 'title' => 'Seeds'],
            [
                'section_key' => 'seeds-category-'.$categories['seeds']->id,
                'title' => 'Seeds',
                'section_type' => 'product_section',
                'layout_type' => 'product_slider',
                'source_type' => 'category_products',
                'category_id' => $categories['seeds']->id,
                'product_limit' => 8,
                'item_limit' => 0,
                'sort_order' => 12,
                'is_active' => true,
            ]
        );

        $service = $this->upsertSection(
            ['section_key' => 'index5-store-services-bottom'],
            [
                'section_key' => 'index5-store-services-bottom',
                'title' => 'Store Services',
                'subtitle' => 'Support blocks at the bottom',
                'section_type' => 'service_section',
                'layout_type' => 'service_grid',
                'source_type' => 'services',
                'category_id' => null,
                'product_limit' => 0,
                'item_limit' => 5,
                'sort_order' => max(19, $lastProductSectionSortOrder + 1),
                'is_active' => true,
            ]
        );

        return [
            'top_selling' => $topSelling,
            'offer' => $offer,
            'animal_healthcare' => $animalHealthcare,
            'strip' => $strip,
            'seeds' => $seeds,
            'service' => $service,
        ];
    }

    private function upsertSection(array $lookup, array $data): ProductHomepageSection
    {
        $section = ProductHomepageSection::query()->where($lookup)->first();

        if (! $section && isset($data['title'], $data['section_type'])) {
            $section = ProductHomepageSection::query()
                ->where('title', $data['title'])
                ->where('section_type', $data['section_type'])
                ->first();
        }

        if (! $section && isset($data['section_key'])) {
            $section = ProductHomepageSection::query()
                ->where('section_key', $data['section_key'])
                ->first();
        }

        if ($section) {
            $section->fill($data);
            $section->save();

            return $section->fresh();
        }

        return ProductHomepageSection::query()->create($data);
    }

    private function seedProducts(
        array $categories,
        array $sections,
        int $brandId,
        int $unitId,
        int $productTypeId
    ): void {
        $offerStart = Carbon::create(2026, 8, 8, 9, 0, 0);
        $offerEnd = Carbon::create(2026, 8, 15, 23, 59, 59);

        $definitions = [
            [
                'sku' => 'AHC-TS-001',
                'name' => 'Animal Health Tonic Plus',
                'category_slug' => 'animal-healthcare',
                'product_type' => 'veterinary',
                'hsn_code' => '30049099',
                'mrp' => 890,
                'customer_price' => 790,
                'dealer_price' => 690,
                'sort_order' => 1,
                'homepage_sort_order' => 1,
                'is_top_selling' => true,
                'is_deal_timer_product' => true,
                'is_offer_active' => true,
                'offer_start_at' => $offerStart,
                'offer_end_at' => $offerEnd,
                'homepage_section_id' => $sections['top_selling']->id,
                'image_path' => self::IMAGE_BASE.'/products/animal-health-tonic-plus.png',
            ],
            [
                'sku' => 'AHC-TS-002',
                'name' => 'Calcium Booster Vet Liquid',
                'category_slug' => 'animal-healthcare',
                'product_type' => 'veterinary',
                'hsn_code' => '30049099',
                'mrp' => 760,
                'customer_price' => 680,
                'dealer_price' => 595,
                'sort_order' => 2,
                'homepage_sort_order' => 2,
                'is_top_selling' => true,
                'is_deal_timer_product' => false,
                'is_offer_active' => false,
                'homepage_section_id' => null,
                'image_path' => self::IMAGE_BASE.'/products/calcium-booster-vet-liquid.png',
            ],
            [
                'sku' => 'AHC-TS-003',
                'name' => 'Mineral Mixture Daily Care',
                'category_slug' => 'animal-healthcare',
                'product_type' => 'veterinary',
                'hsn_code' => '23099090',
                'mrp' => 540,
                'customer_price' => 485,
                'dealer_price' => 430,
                'sort_order' => 3,
                'homepage_sort_order' => 3,
                'is_top_selling' => true,
                'is_deal_timer_product' => false,
                'is_offer_active' => false,
                'homepage_section_id' => null,
                'image_path' => self::IMAGE_BASE.'/products/mineral-mixture-daily-care.png',
            ],
            [
                'sku' => 'R12-SEED-001',
                'name' => 'Hybrid Tomato Seeds',
                'category_slug' => 'seeds',
                'product_type' => 'seed',
                'hsn_code' => '12099990',
                'mrp' => 370,
                'customer_price' => 330,
                'dealer_price' => 270,
                'sort_order' => 4,
                'homepage_sort_order' => 4,
                'is_top_selling' => true,
                'is_deal_timer_product' => false,
                'is_offer_active' => false,
                'homepage_section_id' => null,
                'image_path' => self::IMAGE_BASE.'/products/hybrid-tomato-seeds.png',
            ],
            [
                'sku' => 'R12-SEED-002',
                'name' => 'Hybrid Chilli Seeds',
                'category_slug' => 'seeds',
                'product_type' => 'seed',
                'hsn_code' => '12099990',
                'mrp' => 420,
                'customer_price' => 370,
                'dealer_price' => 310,
                'sort_order' => 5,
                'homepage_sort_order' => 5,
                'is_top_selling' => true,
                'is_deal_timer_product' => false,
                'is_offer_active' => false,
                'homepage_section_id' => null,
                'image_path' => self::IMAGE_BASE.'/products/hybrid-chilli-seeds.png',
            ],
            [
                'sku' => 'R12-SEED-003',
                'name' => 'Okra Seeds Premium',
                'category_slug' => 'seeds',
                'product_type' => 'seed',
                'hsn_code' => '12099990',
                'mrp' => 460,
                'customer_price' => 410,
                'dealer_price' => 340,
                'sort_order' => 6,
                'homepage_sort_order' => 6,
                'is_top_selling' => false,
                'is_deal_timer_product' => false,
                'is_offer_active' => false,
                'homepage_section_id' => null,
                'image_path' => self::IMAGE_BASE.'/products/okra-seeds-premium.png',
            ],
            [
                'sku' => 'R12-SEED-004',
                'name' => 'Cucumber Seeds',
                'category_slug' => 'seeds',
                'product_type' => 'seed',
                'hsn_code' => '12099990',
                'mrp' => 510,
                'customer_price' => 450,
                'dealer_price' => 370,
                'sort_order' => 7,
                'homepage_sort_order' => 7,
                'is_top_selling' => false,
                'is_deal_timer_product' => false,
                'is_offer_active' => false,
                'homepage_section_id' => null,
                'image_path' => self::IMAGE_BASE.'/products/cucumber-seeds.png',
            ],
        ];

        foreach ($definitions as $definition) {
            $category = $categories[$definition['category_slug']];

            $product = Product::query()->updateOrCreate(
                ['sku' => $definition['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brandId,
                    'product_type_id' => $productTypeId,
                    'unit_id' => $unitId,
                    'name' => $definition['name'],
                    'product_type' => $definition['product_type'],
                    'hsn_code' => $definition['hsn_code'],
                    'gst_percent' => 5,
                    'mrp' => $definition['mrp'],
                    'customer_price' => $definition['customer_price'],
                    'dealer_price' => $definition['dealer_price'],
                    'description' => $definition['name'].' for Bawaskar storefront homepage sections.',
                    'short_description' => $definition['name'].' for index-5 store display.',
                    'additional_info' => [
                        ['label' => 'Brand', 'value' => 'Bawaskar Technology'],
                        ['label' => 'Package Information', 'value' => 'Bottle'],
                        ['label' => 'Storage', 'value' => 'Keep product in dry place and use as per label instructions.'],
                    ],
                    'care_instructions' => 'Store in a cool and dry place.',
                    'seller_name' => 'Bawaskar Technology',
                    'seller_address' => 'Pune, Maharashtra',
                    'seller_contact' => '9000000001',
                    'sale_badge_text' => $definition['is_top_selling'] ? 'Best Seller' : null,
                    'low_stock_text' => 'Limited stock available',
                    'is_offer_active' => $definition['is_offer_active'],
                    'offer_start_at' => $definition['offer_start_at'] ?? null,
                    'offer_end_at' => $definition['offer_end_at'] ?? null,
                    'is_visible_to_customers' => true,
                    'is_visible_to_dealers' => true,
                    'is_featured' => $definition['is_top_selling'],
                    'is_top_selling' => $definition['is_top_selling'],
                    'is_trending' => $definition['is_top_selling'],
                    'is_new_arrival' => false,
                    'is_deal_timer_product' => $definition['is_deal_timer_product'],
                    'is_offer_product' => $definition['is_offer_active'],
                    'show_on_homepage' => true,
                    'homepage_section_id' => $definition['homepage_section_id'],
                    'homepage_title' => $definition['name'],
                    'homepage_description' => $definition['name'].' for index-5 storefront display.',
                    'homepage_image_path' => $definition['image_path'],
                    'homepage_button_text' => 'Shop Now',
                    'homepage_button_url' => '/shop-left-sidebar',
                    'sort_order' => $definition['sort_order'],
                    'homepage_sort_order' => $definition['homepage_sort_order'],
                    'is_active' => true,
                ]
            );

            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'path' => $definition['image_path']],
                ['is_primary' => true, 'sort_order' => 1]
            );
        }
    }

    private function seedOfferItems(ProductHomepageSection $section): void
    {
        $items = [
            [
                'slot' => 'left',
                'title' => 'Healthy Cattle Care Range',
                'subtitle' => 'Animal Wellness Offer',
                'description' => 'Feature banner placed above Animal Healthcare section on index-5.',
                'button_text' => 'Shop Now',
                'button_url' => '/shop-left-sidebar',
                'image_path' => self::IMAGE_BASE.'/sections/offers/offer-animal-healthcare-left.png',
                'mobile_image_path' => self::IMAGE_BASE.'/sections/offers/offer-animal-healthcare-left-mobile.png',
                'sort_order' => 1,
            ],
            [
                'slot' => 'right',
                'title' => 'Seed Season Savings',
                'subtitle' => 'High Germination Picks',
                'description' => 'Secondary banner for the offer zone.',
                'button_text' => 'Explore',
                'button_url' => '/shop-left-sidebar',
                'image_path' => self::IMAGE_BASE.'/sections/offers/offer-animal-healthcare-right.png',
                'mobile_image_path' => self::IMAGE_BASE.'/sections/offers/offer-animal-healthcare-right-mobile.png',
                'sort_order' => 2,
            ],
        ];

        foreach ($items as $item) {
            ProductHomepageSectionItem::query()->updateOrCreate(
                ['section_id' => $section->id, 'slot' => $item['slot']],
                $item + ['section_id' => $section->id, 'is_active' => true]
            );
        }
    }

    private function seedStripOfferItem(ProductHomepageSection $section, Category $seedCategory): void
    {
        ProductHomepageSectionItem::query()->updateOrCreate(
            ['section_id' => $section->id, 'slot' => 'strip-main'],
            [
                'section_id' => $section->id,
                'slot' => 'strip-main',
                'title' => 'Seed Collection Offer',
                'highlight_text' => 'Fresh Crop Inputs',
                'discount_text' => 'Up to 20% Off',
                'button_text' => 'View Seeds',
                'button_url' => '/category/'.$seedCategory->slug,
                'image_path' => self::IMAGE_BASE.'/sections/offers/strip-offer-seeds.png',
                'background_color' => '#ffe7c2',
                'text_color' => '#5b3200',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }

    private function seedServiceItems(ProductHomepageSection $section): void
    {
        $items = [
            ['slot' => 'service-1', 'title' => 'Free Shipping', 'subtitle' => 'Free shipping on eligible orders', 'icon_key' => 'shipping', 'sort_order' => 1],
            ['slot' => 'service-2', 'title' => '24 x 7 Service', 'subtitle' => 'Support available throughout the day', 'icon_key' => 'service', 'sort_order' => 2],
            ['slot' => 'service-3', 'title' => 'Online Payment', 'subtitle' => 'Secure online payment options', 'icon_key' => 'pay', 'sort_order' => 3],
            ['slot' => 'service-4', 'title' => 'Festival Offers', 'subtitle' => 'Seasonal discounts for farmers', 'icon_key' => 'offer', 'sort_order' => 4],
            ['slot' => 'service-5', 'title' => '100% Original', 'subtitle' => 'Trusted and genuine products', 'icon_key' => 'return', 'sort_order' => 5],
        ];

        foreach ($items as $item) {
            ProductHomepageSectionItem::query()->updateOrCreate(
                ['section_id' => $section->id, 'slot' => $item['slot']],
                $item + ['section_id' => $section->id, 'is_active' => true]
            );
        }
    }

    private function ensurePositiveStockForAllProducts(int $warehouseId): void
    {
        Product::query()
            ->with('inventoryBatches')
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->each(function (Product $product) use ($warehouseId): void {
                $available = (float) $product->inventoryBatches
                    ->sum(fn (InventoryBatch $batch): float => max(0, (float) $batch->quantity - (float) $batch->reserved_quantity));

                if ($available > 0) {
                    return;
                }

                $quantity = $product->id % 2 === 0 ? 5 : 2;

                InventoryBatch::query()->create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $product->id,
                    'batch_no' => 'AUTO-STOCK-'.$product->id.'-'.now()->format('YmdHis'),
                    'manufacturing_date' => now()->subMonth()->toDateString(),
                    'expiry_date' => now()->addYear()->toDateString(),
                    'purchase_price' => $this->purchasePriceFor($product),
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'low_stock_alert' => 1,
                ]);
            });
    }

    private function purchasePriceFor(Product $product): float
    {
        $base = (float) ($product->dealer_price ?: $product->customer_price ?: $product->mrp ?: 100);

        return round(max(1, $base * 0.75), 2);
    }
}
