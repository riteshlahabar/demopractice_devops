<?php

namespace App\Http\Requests\Api\Localization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AppTranslationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app' => ['required', 'string', Rule::in(array_keys((array) config('localization.apps', [])))],
            'locale' => ['required', 'string', 'max:10'],
        ];
    }
}
