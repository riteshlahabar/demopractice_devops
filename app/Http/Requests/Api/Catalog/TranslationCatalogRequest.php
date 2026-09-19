<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Foundation\Http\FormRequest;

final class TranslationCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:10'],
        ];
    }
}
