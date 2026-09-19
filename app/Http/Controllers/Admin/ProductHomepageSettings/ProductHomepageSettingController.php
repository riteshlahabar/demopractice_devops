<?php

namespace App\Http\Controllers\Admin\ProductHomepageSettings;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductHomepageSettingController extends AdminModuleController
{
    protected string $moduleKey = 'homepage-settings';

    protected function rules(array $module, $record = null): array
    {
        $rules = parent::rules($module, $record);

        $uniqueSortOrder = Rule::unique('product_homepage_sections', 'sort_order');

        if ($record) {
            $uniqueSortOrder->ignore($record->getKey());
        }

        $rules['sort_order'] = ['required', 'integer', 'min:0', $uniqueSortOrder];

        return $rules;
    }

    protected function validationMessages(array $module): array
    {
        return array_merge(parent::validationMessages($module), [
            'sort_order.required' => 'Enter row order.',
            'sort_order.integer' => 'Row order must be a number.',
            'sort_order.min' => 'Row order must be 0 or greater.',
            'sort_order.unique' => 'This row order already exists. Change the previous row order or enter a new row order.',
        ]);
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        $sectionType = (string) ($data['section_type'] ?? $request->input('section_type'));

        $data['source_type'] = $this->sourceTypeForSection($sectionType);

        if ($sectionType !== 'product_section') {
            $data['category_id'] = null;
        }

        if (empty($data['product_limit'])) {
            $data['product_limit'] = 8;
        }

        if (! isset($data['sort_order']) || $data['sort_order'] === '') {
            $data['sort_order'] = 0;
        }

        return $data;
    }

    private function sourceTypeForSection(string $sectionType): string
    {
        return match ($sectionType) {
            'hero_slider',
            'top_small_banners',
            'offer_section' => 'banners',

            'category_section' => 'categories',

            'product_section' => 'category_products',

            'coupon_section' => 'coupon_items',

            'top_selling_section' => 'top_selling_products',

            'strip_offer_banner' => 'text',

            'service_section' => 'services',

            'blog_section' => 'blog_items',

            default => 'none',
        };
    }
}
