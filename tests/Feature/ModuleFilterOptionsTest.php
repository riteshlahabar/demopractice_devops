<?php

namespace Tests\Feature;

use App\Contracts\Admin\Modules\ModuleFormDataContract;
use App\Contracts\Admin\Modules\ModuleQueryContract;
use Illuminate\Http\Request;
use Tests\TestCase;

class ModuleFilterOptionsTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function productsModule(): array
    {
        return config('admin.modules.products') + ['key' => 'products'];
    }

    public function test_the_products_listing_declares_a_section_title_filter(): void
    {
        $filters = collect($this->productsModule()['filters'] ?? []);

        $section = $filters->firstWhere('name', 'homepage_section_id');

        $this->assertNotNull($section, 'Products should offer a Section Title filter.');
        $this->assertSame('Section Title', $section['label']);
        $this->assertSame('homepage_section_id', $section['column']);
        $this->assertSame('title', $section['option_label']);
    }

    public function test_filter_options_are_only_built_for_filters_that_declare_choices(): void
    {
        $formData = app(ModuleFormDataContract::class);

        // The sale orders channel filter has no options of its own, so the
        // toolbar leaves it to the dedicated Channel select.
        $orders = $formData->filterOptions(config('admin.modules.orders'));

        $this->assertArrayNotHasKey('type', $orders);
    }

    public function test_a_filter_with_static_options_is_returned_as_given(): void
    {
        $options = app(ModuleFormDataContract::class)->filterOptions([
            'filters' => [
                ['name' => 'flavour', 'options' => ['a' => 'Alpha', 'b' => 'Beta']],
            ],
        ]);

        $this->assertSame(['a' => 'Alpha', 'b' => 'Beta'], $options['flavour']);
    }

    public function test_a_module_without_filters_produces_none(): void
    {
        $this->assertSame([], app(ModuleFormDataContract::class)->filterOptions(['fields' => []]));
    }

    /**
     * The toolbar submits the filter as a query parameter, so the listing query
     * has to narrow on it - otherwise the dropdown would look like it works but
     * return everything.
     */
    public function test_choosing_a_section_narrows_the_products_query(): void
    {
        $request = Request::create('/admin/products', 'GET', ['homepage_section_id' => 7]);

        $sql = app(ModuleQueryContract::class)->filtered($request, $this->productsModule())->toSql();

        $this->assertStringContainsString('homepage_section_id', $sql);
    }

    /**
     * The per-column dropdown was removed, so a search always spans every
     * column the module lists and cannot be narrowed from the URL.
     */
    public function test_search_covers_every_configured_column(): void
    {
        $module = $this->productsModule();
        $request = Request::create('/admin/products', 'GET', ['search' => 'tonic', 'search_column' => 'sku']);

        $sql = app(ModuleQueryContract::class)->filtered($request, $module)->toSql();

        foreach ($module['search'] as $column) {
            $this->assertStringContainsString($column, $sql, "Search should still cover {$column}.");
        }
    }

    public function test_no_selection_leaves_the_query_unfiltered(): void
    {
        $request = Request::create('/admin/products', 'GET', ['homepage_section_id' => '']);

        $sql = app(ModuleQueryContract::class)->filtered($request, $this->productsModule())->toSql();

        $this->assertStringNotContainsString('homepage_section_id', $sql, 'An empty choice means "All".');
    }
}
