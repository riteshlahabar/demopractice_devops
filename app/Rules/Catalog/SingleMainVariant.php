<?php

namespace App\Rules\Catalog;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * SRP: guarantees the variant repeater posts exactly one active main variant.
 *
 * The main variant is what direct Add to Cart uses and what mirrors its price,
 * unit, HSN and GST onto the product row, so a product without one - or with
 * two - would silently pick the wrong pack.
 */
final class SingleMainVariant implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rows = is_array($value) ? $value : [];

        $active = collect($rows)->filter(
            fn ($row): bool => is_array($row) && filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL),
        );

        if ($active->isEmpty()) {
            $fail('Add at least one active size / pack variant.');

            return;
        }

        $mainCount = $active->filter(
            fn (array $row): bool => filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOL),
        )->count();

        if ($mainCount === 0) {
            $fail('Mark one active variant as the Main Product.');
        }

        if ($mainCount > 1) {
            $fail('Only one variant can be marked as the Main Product.');
        }
    }
}
