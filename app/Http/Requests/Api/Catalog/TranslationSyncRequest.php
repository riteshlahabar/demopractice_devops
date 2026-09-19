<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Foundation\Http\FormRequest;

final class TranslationSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:10'],
            'items' => ['required', 'array', 'min:1', 'max:'.(int) config('localization.app_sync_max_items', 400)],
            'items.*' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function items(): array
    {
        /** @var array<string, string> $items */
        $items = $this->validated('items');

        return $items;
    }
}
