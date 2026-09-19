<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps the existing text column backward compatible while exposing
 * Additional Information as structured label/value rows.
 */
final class ProductAdditionalInformation implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode((string) $value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->normalize($decoded);
        }

        return [['label' => 'Additional Information', 'value' => (string) $value]];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_string($value)) {
            return blank($value) ? null : $value;
        }

        $rows = $this->normalize(is_array($value) ? $value : []);

        return $rows === []
            ? null
            : json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function normalize(array $rows): array
    {
        if (! array_is_list($rows)) {
            $rows = collect($rows)
                ->map(fn (mixed $value, mixed $label): array => ['label' => $label, 'value' => $value])
                ->values()
                ->all();
        }

        return collect($rows)
            ->map(function (mixed $row): array {
                $row = is_array($row) ? $row : [];

                return [
                    'label' => trim((string) ($row['label'] ?? '')),
                    'value' => trim((string) ($row['value'] ?? '')),
                ];
            })
            ->filter(fn (array $row): bool => $row['label'] !== '' || $row['value'] !== '')
            ->values()
            ->all();
    }
}
