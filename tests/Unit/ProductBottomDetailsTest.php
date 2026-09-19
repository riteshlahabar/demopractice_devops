<?php

namespace Tests\Unit;

use App\Casts\KeyValueRows;
use App\Models\Catalog\Product;
use App\Rules\Catalog\SingleMainVariant;
use App\Support\Admin\Forms\FormFieldTree;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductBottomDetailsTest extends TestCase
{
    public function test_bottom_details_children_are_nested_under_their_container(): void
    {
        $nodes = (new FormFieldTree)->build([
            ['type' => 'section_heading', 'label' => '11. Bottom Details'],
            ['type' => 'product_bottom_details', 'label' => 'Bottom Details', 'groups' => ['description' => 'Description']],
            ['name' => 'description', 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
            ['name' => 'care_instructions', 'render_inside' => 'product_bottom_details', 'render_group' => 'care_instructions'],
            ['name' => 'meta_title'],
        ]);

        $this->assertCount(3, $nodes, 'Nested fields must not stay at the top level.');
        $this->assertSame('product_bottom_details', $nodes[1]['field']['type']);
        $this->assertCount(2, $nodes[1]['children']);
        $this->assertSame('description', $nodes[1]['children'][0]['name']);
        $this->assertSame('meta_title', $nodes[2]['field']['name']);
    }

    public function test_a_field_naming_an_unknown_container_stays_at_the_top_level(): void
    {
        $nodes = (new FormFieldTree)->build([
            ['name' => 'description', 'render_inside' => 'not_registered'],
        ]);

        $this->assertCount(1, $nodes);
        $this->assertSame([], $nodes[0]['children']);
    }

    public function test_display_only_and_create_only_fields_are_skipped(): void
    {
        $this->assertFalse((new FormFieldTree)->shouldRender(['name' => 'meta_title', 'display_only' => true], false));
        $this->assertFalse((new FormFieldTree)->shouldRender(['name' => 'password', 'create_only' => true], true));
        $this->assertTrue((new FormFieldTree)->shouldRender(['name' => 'password', 'create_only' => true], false));
        $this->assertTrue((new FormFieldTree)->shouldRender(['name' => 'name'], true));
    }

    public function test_additional_information_rows_survive_a_save_and_load_round_trip(): void
    {
        $cast = new KeyValueRows;
        $product = new Product;

        $stored = $cast->set($product, 'additional_info', [
            ['label' => 'Net Quantity', 'value' => '500 ML'],
            ['label' => '  ', 'value' => '  '],
        ], []);

        $this->assertSame(
            [['label' => 'Net Quantity', 'value' => '500 ML']],
            $cast->get($product, 'additional_info', $stored, []),
        );
    }

    public function test_legacy_free_text_additional_information_is_kept_as_a_single_row(): void
    {
        $cast = new KeyValueRows;
        $product = new Product;

        $this->assertSame(
            [['label' => '', 'value' => 'Keep product in a dry place.']],
            $cast->get($product, 'additional_info', 'Keep product in a dry place.', []),
        );

        $this->assertSame([], $cast->get($product, 'additional_info', null, []));
    }

    #[DataProvider('mainVariantProvider')]
    public function test_single_main_variant_rule(array $variants, ?string $expectedFailure): void
    {
        $failure = null;
        (new SingleMainVariant)->validate('variants', $variants, function (string $message) use (&$failure): void {
            $failure ??= $message;
        });

        $this->assertSame($expectedFailure, $failure);
    }

    public static function mainVariantProvider(): array
    {
        return [
            'exactly one main' => [
                [['is_active' => true, 'is_default' => true], ['is_active' => true, 'is_default' => false]],
                null,
            ],
            'no main marked' => [
                [['is_active' => true, 'is_default' => false]],
                'Mark one active variant as the Main Product.',
            ],
            'two mains marked' => [
                [['is_active' => true, 'is_default' => true], ['is_active' => true, 'is_default' => true]],
                'Only one variant can be marked as the Main Product.',
            ],
            'no active variant' => [
                [['is_active' => false, 'is_default' => true]],
                'Add at least one active size / pack variant.',
            ],
            'empty repeater' => [[], 'Add at least one active size / pack variant.'],
        ];
    }
}
