<?php

namespace App\Http\Requests\Api\Localization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AppTranslationRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app' => ['required', 'string', Rule::in(array_keys((array) config('localization.apps', [])))],
            'items' => ['required', 'array', 'max:'.(int) config('localization.app_register_max_items', 1500)],
            'items.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Only well-formed `group.name` keys are kept, so the endpoint cannot be
     * used to fill the table with arbitrary junk.
     *
     * @return array<string, string>
     */
    public function items(): array
    {
        $items = [];

        foreach ((array) $this->validated('items') as $key => $text) {
            if (is_string($key) && preg_match('/^[a-z0-9_]+(\.[a-z0-9_]+)+$/', $key) === 1 && strlen($key) <= 190) {
                $items[$key] = (string) $text;
            }
        }

        return $items;
    }
}
