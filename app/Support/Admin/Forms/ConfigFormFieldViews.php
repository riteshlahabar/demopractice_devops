<?php

namespace App\Support\Admin\Forms;

use App\Contracts\Admin\FormFieldViewContract;
use Illuminate\Contracts\Config\Repository;

final class ConfigFormFieldViews implements FormFieldViewContract
{
    public function __construct(private readonly Repository $config) {}

    public function resolve(string $type): ?array
    {
        $custom = $this->config->get('admin_form_fields.'.$type);

        if (! is_array($custom) || blank($custom['view'] ?? null)) {
            return null;
        }

        return [
            'view' => (string) $custom['view'],
            'wrap' => (bool) ($custom['wrap'] ?? true),
        ];
    }
}
