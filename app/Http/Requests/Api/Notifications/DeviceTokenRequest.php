<?php

namespace App\Http\Requests\Api\Notifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', Rule::in((array) config('notifications.device_platforms', []))],
        ];
    }
}
