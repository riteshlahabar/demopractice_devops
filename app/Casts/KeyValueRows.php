<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * SRP: stores a repeatable label/value table in a single text column.
 *
 * Used by the product "Additional Information" block, which the storefront
 * renders as the two column `info-table` from the Fastkart detail page. The
 * column previously held free text, so legacy content is surfaced as a single
 * value-only row instead of being thrown away.
 *
 * @implements CastsAttributes<array<int, array{label: string, value: string}>, mixed>
 */
final class KeyValueRows implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (blank($value)) {
            return [];
        }

        if (is_array($value)) {
            return $this->normalize($value);
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded)
            ? $this->normalize($decoded)
            : [['label' => '', 'value' => (string) $value]];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (blank($value)) {
            return null;
        }

        $rows = $this->normalize(is_array($value) ? $value : (array) json_decode((string) $value, true));

        return $rows === [] ? null : json_encode($rows, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  array<mixed>  $rows
     * @return array<int, array{label: string, value: string}>
     */
    private function normalize(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row): array => [
                'label' => trim((string) (is_array($row) ? ($row['label'] ?? '') : '')),
                'value' => trim((string) (is_array($row) ? ($row['value'] ?? '') : $row)),
            ])
            ->reject(fn (array $row): bool => $row['label'] === '' && $row['value'] === '')
            ->values()
            ->all();
    }
}
