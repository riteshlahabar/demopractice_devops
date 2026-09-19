<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Services\Catalog\Product\ProductVariantFormDataService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ProductVariantFormDataTest extends TestCase
{
    private function service(): ProductVariantFormDataService
    {
        $units = new class implements ProductVariantUnitContract
        {
            public function options(): array
            {
                return [7 => 'Kilogram (KG)'];
            }

            public function shortNameFor(int|string|null $unitId): ?string
            {
                return (int) $unitId === 7 ? 'KG' : null;
            }

            public function idForShortName(?string $shortName): ?int
            {
                return strtoupper((string) $shortName) === 'KG' ? 7 : null;
            }
        };

        return new ProductVariantFormDataService($units);
    }

    private function product(array $attributes): Product
    {
        $product = new Product($attributes);
        $product->exists = true;
        $product->setAttribute('id', 55);

        return $product;
    }

    public function test_a_product_without_any_variant_keeps_its_existing_price_and_tax_details(): void
    {
        $product = $this->product([
            'sku' => 'OLD-001',
            'unit_id' => 7,
            'hsn_code' => '3004',
            'gst_percent' => 12,
            'mrp' => 600,
            'dealer_price' => 480,
            'customer_price' => 550,
        ]);
        $product->setRelation('variants', new Collection);

        $rows = $this->service()->rowsFor($product);

        $this->assertCount(1, $rows, 'An empty repeater would hide the prices the product already has.');
        $this->assertSame(600.0, (float) $rows[0]['mrp']);
        $this->assertSame(480.0, (float) $rows[0]['dealer_price']);
        $this->assertSame(550.0, (float) $rows[0]['customer_price']);
        $this->assertSame('3004', $rows[0]['hsn_code']);
        $this->assertSame(12.0, (float) $rows[0]['gst_percent']);
        $this->assertSame('OLD-001', $rows[0]['variant_sku']);
        $this->assertSame(7, $rows[0]['unit_id']);
        $this->assertSame('KG', $rows[0]['size_unit']);
        $this->assertTrue($rows[0]['is_default']);
        // The real pack size is unknown, so the admin has to enter it.
        $this->assertNull($rows[0]['size_value']);
    }

    public function test_inactive_variants_are_shown_when_none_are_active(): void
    {
        $product = $this->product(['mrp' => 600]);
        $product->setRelation('variants', new Collection([
            new ProductVariant([
                'size_value' => 5, 'size_unit' => 'KG', 'value' => '5 KG', 'unit_id' => 7,
                'mrp' => 300, 'dealer_price' => 250, 'customer_price' => 280,
                'is_active' => false, 'is_default' => true,
            ]),
        ]));

        $rows = $this->service()->rowsFor($product);

        $this->assertCount(1, $rows);
        $this->assertSame(300.0, (float) $rows[0]['mrp']);
        $this->assertFalse($rows[0]['is_active']);
    }

    public function test_active_variants_are_preferred_over_inactive_ones(): void
    {
        $product = $this->product(['mrp' => 600]);
        $product->setRelation('variants', new Collection([
            new ProductVariant([
                'size_value' => 1, 'size_unit' => 'KG', 'unit_id' => 7,
                'mrp' => 100, 'is_active' => false, 'is_default' => false,
            ]),
            new ProductVariant([
                'size_value' => 5, 'size_unit' => 'KG', 'unit_id' => 7,
                'mrp' => 500, 'is_active' => true, 'is_default' => true,
            ]),
        ]));

        $rows = $this->service()->rowsFor($product);

        $this->assertCount(1, $rows);
        $this->assertSame(500.0, (float) $rows[0]['mrp']);
    }
}
