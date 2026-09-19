<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionProductRepositoryContract;
use App\Contracts\Storefront\Session\StorefrontCartStorageContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\User;
use App\Services\Storefront\Session\StorefrontCartSummaryService;
use App\Services\Storefront\Session\StorefrontSessionProductRules;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class StorefrontCartSummaryServiceTest extends TestCase
{
    public function test_dealer_case_pricing_and_checkout_quantities_are_preserved(): void
    {
        $product = new Product;
        $product->id = 1;
        $product->gst_percent = 18;

        $variant = new ProductVariant([
            'product_id' => 1,
            'units_per_case' => 12,
            'dealer_price' => 10,
            'customer_price' => 12,
            'stock_quantity' => 100,
            'is_active' => true,
        ]);
        $variant->id = 2;
        $variant->setRelation('product', $product);
        $product->setRelation('variants', collect([$variant]));

        $storage = new class implements StorefrontCartStorageContract
        {
            public function add(
                Request $request,
                Product $product,
                float $quantity,
                ?ProductVariant $variant = null
            ): void {}

            public function update(Request $request, array $items): void {}

            public function remove(Request $request, string $lineKey): void {}

            public function clear(Request $request): void {}

            public function cart(Request $request): array
            {
                return [
                    '1:2' => [
                        'product_id' => 1,
                        'variant_id' => 2,
                        'quantity' => 2.0,
                    ],
                ];
            }
        };

        $identity = new class implements StorefrontIdentitySessionContract
        {
            public function user(Request $request): ?User
            {
                return null;
            }

            public function audience(Request $request): string
            {
                return 'dealer';
            }

            public function login(Request $request, User $user): void {}

            public function logout(Request $request): void {}
        };

        $products = new class($product) implements StorefrontSessionProductRepositoryContract
        {
            public function __construct(private readonly Product $product) {}

            public function visibleByIds(string $audience, array $productIds): Collection
            {
                return collect([$this->product->id => $this->product]);
            }
        };

        $service = new StorefrontCartSummaryService(
            $storage,
            $identity,
            $products,
            new StorefrontSessionProductRules
        );
        $request = Request::create('/cart');
        $summary = $service->summary($request);
        $item = $summary['items']->first();

        $this->assertSame(2.0, $summary['count']);
        $this->assertSame(203.39, $summary['subtotal']);
        $this->assertSame(36.61, $summary['gst_total']);
        $this->assertSame(240.0, $summary['grand_total']);
        $this->assertSame(24.0, $item['unit_quantity']);
        $this->assertSame('case(s)', $item['quantity_label']);
        $this->assertSame([
            [
                'product_id' => 1,
                'variant_id' => 2,
                'quantity' => 24.0,
                'pack_quantity' => 2.0,
                'units_per_case' => 12.0,
            ],
        ], $service->checkoutItems($request));
    }
}
