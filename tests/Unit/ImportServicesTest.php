<?php

namespace Tests\Unit;

use App\Data\Admin\ImportResult;
use App\Services\Admin\Imports\ImportImagePathNormalizer;
use App\Services\Admin\Imports\ImportRowMapper;
use App\Services\Admin\Imports\ImportRowReader;
use App\Services\Admin\Imports\ImportSampleBuilder;
use App\Services\Admin\Imports\SpreadsheetImportReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImportServicesTest extends TestCase
{
    private function mapper(): ImportRowMapper
    {
        return new ImportRowMapper(new ImportRowReader, new ImportImagePathNormalizer);
    }

    public function test_headers_are_normalised_so_any_capitalisation_matches(): void
    {
        $reader = new ImportRowReader;

        $this->assertSame('product_name', $reader->header('Product Name'));
        $this->assertSame('hsn_code', $reader->header('HSN-Code'));
        $this->assertSame('gst_percent', $reader->header('GST Percent'));

        // Trailing punctuation is dropped, so a "GST %" column header does not
        // match the gst_percent field - the sample file uses gst_percent.
        $this->assertSame('gst', $reader->header('  GST % '));
    }

    public function test_first_filled_skips_blank_columns(): void
    {
        $reader = new ImportRowReader;
        $row = ['product_sku' => '  ', 'sku' => 'PES001'];

        $this->assertSame('PES001', $reader->firstFilled($row, ['product_sku', 'sku']));
        $this->assertNull($reader->firstFilled($row, ['missing']));
    }

    #[DataProvider('imagePathProvider')]
    public function test_image_paths_are_confined_to_the_uploads_folder(string $input, string $module, string $expected): void
    {
        $this->assertSame($expected, (new ImportImagePathNormalizer)->normalize($input, $module));
    }

    public static function imagePathProvider(): array
    {
        return [
            'plain name' => ['calcium.jpg', 'products', 'uploads/products/import/calcium.jpg'],
            'already under uploads' => ['uploads/products/a.jpg', 'products', 'uploads/products/a.jpg'],
            'banner module' => ['b.jpg', 'storefront-banners', 'uploads/storefront/banners/home-import/b.jpg'],
            'other module' => ['c.jpg', 'brands', 'uploads/imports/brands/c.jpg'],
            'traversal refused' => ['../../etc/passwd', 'products', ''],
            'remote url refused' => ['https://evil.test/x.jpg', 'products', ''],
            'blank' => ['', 'products', ''],
        ];
    }

    public function test_gallery_cells_split_on_pipe_and_semicolon_and_deduplicate(): void
    {
        $paths = (new ImportImagePathNormalizer)->galleryPaths('a.jpg|b.jpg;a.jpg| ');

        $this->assertSame([
            'uploads/products/import/a.jpg',
            'uploads/products/import/b.jpg',
        ], $paths);
    }

    public function test_row_values_are_cast_to_the_right_types(): void
    {
        $row = [
            'name' => 'Premium Pesticide',
            'is_active' => 'yes',
            'sort_order' => '3',
            'mrp' => '499.50',
            'skipped' => 'ignored',
        ];

        $data = $this->mapper()->map($row, ['name', 'is_active', 'sort_order', 'mrp'], 'products');

        $this->assertSame('Premium Pesticide', $data['name']);
        $this->assertTrue($data['is_active']);
        $this->assertSame(3, $data['sort_order']);
        $this->assertSame(499.5, $data['mrp']);
        $this->assertArrayNotHasKey('skipped', $data, 'Only configured fields are imported.');
    }

    public function test_blank_cells_are_left_out_so_they_do_not_overwrite_existing_values(): void
    {
        $data = $this->mapper()->map(['name' => 'Keep', 'mrp' => '   '], ['name', 'mrp'], 'products');

        $this->assertSame(['name' => 'Keep'], $data);
    }

    public function test_a_category_row_gets_a_slug_generated_from_its_name(): void
    {
        $data = $this->mapper()->map(['name' => 'Crop Care'], ['name'], 'categories');

        $this->assertSame('crop-care', $data['slug']);
    }

    #[DataProvider('uniqueKeyProvider')]
    public function test_unique_keys_decide_update_or_create(string $module, array $data, array $expected): void
    {
        $this->assertSame($expected, $this->mapper()->uniqueKeysFor($data, $module));
    }

    public static function uniqueKeyProvider(): array
    {
        return [
            'product by sku' => ['products', ['sku' => 'PES001', 'name' => 'X'], ['sku' => 'PES001']],
            'product falls back to name' => ['products', ['name' => 'X'], ['name' => 'X']],
            'product with neither always creates' => ['products', [], []],
            'unit by short name' => ['units', ['short_name' => 'ltr', 'name' => 'Litre'], ['short_name' => 'ltr']],
            'unknown module always creates' => ['salesmen', ['name' => 'X'], []],
        ];
    }

    public function test_csv_files_are_read_including_a_utf8_byte_order_mark(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'imp').'.csv';
        file_put_contents($path, "\xEF\xBB\xBFname,mrp\nPesticide,500\n\n");

        $rows = (new SpreadsheetImportReader)->rows($path, 'csv');
        unlink($path);

        $this->assertSame([['name', 'mrp'], ['Pesticide', '500']], $rows, 'Blank lines are dropped and the BOM stripped.');
    }

    public function test_sample_headers_put_the_friendly_columns_first(): void
    {
        $headers = (new ImportSampleBuilder)->headers('products', [
            'fields' => [['name' => 'sku'], ['name' => 'name'], ['type' => 'section_heading']],
        ]);

        $this->assertSame('product_type', $headers[0]);
        $this->assertContains('category_name', $headers);
        $this->assertContains('sku', $headers);
        $this->assertSame(array_unique($headers), $headers);
    }

    public function test_the_sample_row_lines_up_with_its_headers(): void
    {
        $builder = new ImportSampleBuilder;
        $headers = ['sku', 'name', 'unknown_column'];

        $row = $builder->row('products', $headers);

        $this->assertCount(3, $row);
        $this->assertSame('PES001', $row[0]);
        $this->assertSame('', $row[2], 'Columns without an example stay blank.');
    }

    public function test_import_result_reports_failures(): void
    {
        $clean = new ImportResult(created: 2, updated: 1);
        $broken = new ImportResult(created: 1, failed: 1, firstError: 'Line 3: bad');

        $this->assertFalse($clean->hasFailures());
        $this->assertTrue($broken->hasFailures());
        $this->assertSame('Import completed. Created: 2, Updated: 1, Failed: 0.', $clean->summary());
    }
}
