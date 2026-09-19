<?php

namespace App\Support\Admin\Forms;

use App\Contracts\Admin\FormFieldTreeContract;

final class FormFieldTree implements FormFieldTreeContract
{
    public function build(array $fields): array
    {
        $nodes = [];
        $positions = [];

        foreach ($fields as $field) {
            $parent = $field['render_inside'] ?? null;

            if (filled($parent) && isset($positions[$parent])) {
                $nodes[$positions[$parent]]['children'][] = $field;

                continue;
            }

            $nodes[] = ['field' => $field, 'children' => []];

            // A container is addressed by its name, or by its type when it has
            // no name of its own (section wrappers such as Bottom Details).
            $key = $field['name'] ?? ($field['type'] ?? null);
            if (filled($key)) {
                $positions[$key] = array_key_last($nodes);
            }
        }

        return $nodes;
    }

    public function shouldRender(array $field, bool $editing): bool
    {
        if ($field['display_only'] ?? false) {
            return false;
        }

        if (($field['create_only'] ?? false) && $editing) {
            return false;
        }

        return ! (($field['edit_only'] ?? false) && ! $editing);
    }

    public function groups(array $field): array
    {
        return array_filter((array) ($field['groups'] ?? []));
    }
}
