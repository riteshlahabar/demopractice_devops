<?php

namespace App\Services\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleFormDataContract;
use App\Models\Storefront\StorefrontSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class ModuleFormData implements ModuleFormDataContract
{
    public function forCreate(Request $request, array $module): array
    {
        $data = [];

        foreach ($module['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $queryKey = $field['query_key'] ?? $name;

            if ($request->filled($queryKey)) {
                $data[$name] = $request->input($queryKey);
            }
        }

        return $this->applySubmenuDefaults($data, $request, $module);
    }

    public function forRecord(Model $record, array $module): array
    {
        $data = [];

        foreach ($module['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $value = data_get($record, $field['source'] ?? $name);
            $type = $field['type'] ?? null;

            if ($value && $type === 'datetime-local') {
                $value = Carbon::parse($value)->format('Y-m-d\TH:i');
            }

            if ($value && $type === 'date') {
                $value = Carbon::parse($value)->format('Y-m-d');
            }

            $data[$name] = $value;
        }

        return $data;
    }

    public function options(array $module): array
    {
        $options = [];

        foreach ($this->selectFields($module) as $field) {
            if (isset($field['options'])) {
                $options[$field['name']] = $field['options'];

                continue;
            }

            if (! isset($field['option_model'])) {
                continue;
            }

            $label = $field['option_label'] ?? 'name';

            $options[$field['name']] = $this->optionQuery($field)
                ->orderBy($label)
                ->pluck($label, $field['option_value'] ?? 'id')
                ->all();
        }

        return $options;
    }

    public function optionAttributes(array $module): array
    {
        $attributes = [];

        foreach ($this->selectFields($module) as $field) {
            if (empty($field['option_model']) || empty($field['option_attributes'])) {
                continue;
            }

            $labelColumn = $field['option_label'] ?? 'name';
            $valueColumn = $field['option_value'] ?? 'id';
            $attributeMap = (array) $field['option_attributes'];
            $columns = array_values(array_unique(array_merge([$valueColumn, $labelColumn], array_values($attributeMap))));

            foreach ($this->optionQuery($field)->orderBy($labelColumn)->get($columns) as $option) {
                $key = data_get($option, $valueColumn);

                foreach ($attributeMap as $attributeName => $column) {
                    $attributes[$field['name']][$key][$attributeName] = data_get($option, $column);
                }
            }
        }

        return $attributes;
    }

    public function filterOptions(array $module): array
    {
        $options = [];

        foreach ($module['filters'] ?? [] as $filter) {
            $name = $filter['name'] ?? null;

            if (! $name) {
                continue;
            }

            if (isset($filter['options'])) {
                $options[$name] = $filter['options'];

                continue;
            }

            if (! isset($filter['option_model'])) {
                continue;
            }

            $label = $filter['option_label'] ?? 'name';

            $options[$name] = $this->optionQuery($filter)
                ->orderBy($label)
                ->pluck($label, $filter['option_value'] ?? 'id')
                ->all();
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<int, array<string, mixed>>
     */
    private function selectFields(array $module): array
    {
        return array_values(array_filter(
            $module['fields'] ?? [],
            fn (array $field): bool => ($field['type'] ?? null) === 'select' && ! empty($field['name']),
        ));
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function optionQuery(array $field): mixed
    {
        $query = $field['option_model']::query();

        foreach ($field['option_where'] ?? [] as $column => $value) {
            $query->where($column, $value);
        }

        return $query;
    }

    /**
     * Storefront rows are added from a submenu link, so the row the admin came
     * from is pre-selected instead of being typed again.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    private function applySubmenuDefaults(array $data, Request $request, array $module): array
    {
        $key = $module['key'] ?? '';

        if ($key === 'storefront-section-products' && $request->filled('section_key')) {
            $section = StorefrontSection::query()->where('section_key', $request->query('section_key'))->first();

            if ($section) {
                $data['section_id'] = $section->id;
            }
        }

        if ($key === 'storefront-sections' && $request->query('section_key') === 'row_16_blog') {
            $data['title'] ??= (string) $request->query('row_title', 'Bottom Blog');
            $data['section_type'] ??= 'offer';
            $data['source_type'] ??= 'manual';
            $data['product_limit'] ??= 3;
            $data['sort_order'] ??= 16;
            $data['is_active'] ??= true;
        }

        return $data;
    }
}
