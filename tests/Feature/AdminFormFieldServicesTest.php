<?php

namespace Tests\Feature;

use App\Contracts\Admin\FormFieldTreeContract;
use App\Contracts\Admin\FormFieldViewContract;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AdminFormFieldServicesTest extends TestCase
{
    public function test_both_form_field_services_resolve_from_the_container(): void
    {
        $this->assertInstanceOf(FormFieldTreeContract::class, app(FormFieldTreeContract::class));
        $this->assertInstanceOf(FormFieldViewContract::class, app(FormFieldViewContract::class));
    }

    public function test_custom_field_views_come_from_config_not_from_the_class(): void
    {
        $views = app(FormFieldViewContract::class);

        $this->assertSame(
            ['view' => 'admin.products.partials.variants.repeater', 'wrap' => true],
            $views->resolve('product_variants_repeater'),
        );

        // The translation tool partial emits its own grid column.
        $this->assertFalse($views->resolve('product_translation_tools')['wrap']);

        $this->assertNull($views->resolve('text'), 'Plain types fall through to the standard control.');
    }

    public function test_a_new_field_type_needs_no_code_change(): void
    {
        config()->set('admin_form_fields.demo_widget', ['view' => 'demo.widget']);

        $this->assertSame(
            ['view' => 'demo.widget', 'wrap' => true],
            app(FormFieldViewContract::class)->resolve('demo_widget'),
        );
    }

    /**
     * The field partials now read the layout services from variables the view
     * composer supplies instead of calling a static class, so rendering them is
     * what proves the wiring holds.
     */
    private function renderField(array $field, array $children = []): string
    {
        // Normally shared by the web middleware; supplied here because the
        // partial is rendered outside a request.
        view()->share('errors', new ViewErrorBag);

        return view('admin.shared.fields.field', [
            'field' => $field,
            'children' => $children,
            'module' => ['key' => 'products'],
            'record' => null,
            'formData' => [],
            'options' => [],
            'optionAttributes' => [],
            'fieldTree' => app(FormFieldTreeContract::class),
            'fieldViews' => app(FormFieldViewContract::class),
        ])->render();
    }

    public function test_a_plain_field_renders_with_its_label(): void
    {
        $html = $this->renderField(['name' => 'name', 'label' => 'Product Name', 'rules' => ['required']]);

        $this->assertStringContainsString('Product Name', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('required', $html);
    }

    public function test_a_radio_field_renders_one_input_per_option(): void
    {
        $html = $this->renderField([
            'name' => 'detail_banner_position',
            'label' => 'Description Banner Position',
            'type' => 'radio',
            'options' => ['before' => 'Start', 'middle' => 'Middle', 'after' => 'End'],
            'default' => 'after',
        ]);

        $this->assertSame(3, substr_count($html, 'type="radio"'));
        $this->assertStringContainsString('value="after"', $html);
        $this->assertStringContainsString('checked', $html);
    }

    public function test_the_bottom_details_container_renders_its_subsections(): void
    {
        $html = $this->renderField(
            ['type' => 'product_bottom_details', 'label' => 'Bottom Details', 'groups' => [
                'description' => 'Description',
                'care_instructions' => 'Care Instructions',
            ]],
            [
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'render_group' => 'description'],
                ['name' => 'care_instructions', 'label' => 'Care Instructions', 'type' => 'textarea', 'render_group' => 'care_instructions'],
            ],
        );

        $this->assertStringContainsString('Description', $html);
        $this->assertStringContainsString('Care Instructions', $html);
        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('name="care_instructions"', $html);
    }

    public function test_every_registered_custom_view_exists(): void
    {
        foreach (array_keys(config('admin_form_fields')) as $type) {
            $view = app(FormFieldViewContract::class)->resolve($type)['view'];

            $this->assertTrue(view()->exists($view), "Field type {$type} points at a missing view: {$view}");
        }
    }
}
