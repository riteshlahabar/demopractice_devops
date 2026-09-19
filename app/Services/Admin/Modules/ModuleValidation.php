<?php

namespace App\Services\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleValidationContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class ModuleValidation implements ModuleValidationContract
{
    /**
     * Rule name => message template, with :label replaced by the field label.
     */
    private const MESSAGE_TEMPLATES = [
        'required' => 'Enter :label.',
        'required_if' => 'Enter :label.',
        'required_with' => 'Enter :label.',
        'required_without' => 'Enter :label.',
        'email' => 'Enter valid :label.',
        'numeric' => 'Enter valid number for :label.',
        'integer' => 'Enter valid number for :label.',
        'date' => 'Enter valid date for :label.',
        'url' => 'Enter valid URL for :label.',
        'image' => 'Upload valid image for :label.',
        'file' => 'Upload valid file for :label.',
        'exists' => 'Select valid :label.',
        'unique' => ':label already exists.',
        'min' => 'Enter valid :label.',
        'max' => 'Enter valid :label.',
        'in' => 'Select valid :label.',
    ];

    public function rules(array $module, ?Model $record = null): array
    {
        $rules = [];

        foreach ($module['fields'] ?? [] as $field) {
            if (($field['display_only'] ?? false) || ($field['name'] ?? '') === '') {
                continue;
            }

            $fieldRules = $field['rules'] ?? ['nullable'];
            $fieldRules = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            // {id} lets a unique rule ignore the record being edited.
            $fieldRules = array_map(function ($rule) use ($record) {
                return is_string($rule) && str_contains($rule, '{id}')
                    ? str_replace('{id}', $record ? (string) $record->getKey() : 'NULL', $rule)
                    : $rule;
            }, $fieldRules);

            // Editing: a blank password keeps the current one.
            if ($record && ($field['type'] ?? '') === 'password') {
                $fieldRules = array_values(array_filter($fieldRules, fn ($rule) => $rule !== 'required' && $rule !== 'nullable'));
                array_unshift($fieldRules, 'nullable');
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $rules;
    }

    public function messages(array $module): array
    {
        $messages = [];

        foreach ($this->labels($module) as $name => $label) {
            foreach (self::MESSAGE_TEMPLATES as $rule => $template) {
                $messages[$name.'.'.$rule] = str_replace(':label', $label, $template);
            }
        }

        return $messages;
    }

    public function attributes(array $module): array
    {
        return $this->labels($module);
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, string>
     */
    private function labels(array $module): array
    {
        $labels = [];

        foreach ($module['fields'] ?? [] as $field) {
            if (empty($field['name'])) {
                continue;
            }

            $name = (string) $field['name'];
            $labels[$name] = (string) ($field['label'] ?? Str::headline($name));
        }

        return $labels;
    }
}
