<?php

namespace Database\Seeders;

use App\Models\Catalog\Unit;
use Illuminate\Database\Seeder;

/**
 * Packing units offered by the variant "Select Unit" dropdown.
 *
 * These match the short names legacy variants stored directly on `size_unit`,
 * so opening an existing product preselects the right unit instead of showing
 * an empty required field.
 */
class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['short_name' => 'ML', 'name' => 'Millilitre', 'unit_type' => 'volume', 'decimal_precision' => 0],
            ['short_name' => 'LTR', 'name' => 'Litre', 'unit_type' => 'volume', 'decimal_precision' => 3],
            ['short_name' => 'GM', 'name' => 'Gram', 'unit_type' => 'weight', 'decimal_precision' => 0],
            ['short_name' => 'KG', 'name' => 'Kilogram', 'unit_type' => 'weight', 'decimal_precision' => 3],
            ['short_name' => 'PCS', 'name' => 'Pieces', 'unit_type' => 'quantity', 'decimal_precision' => 0],
        ];

        foreach ($units as $unit) {
            $existing = Unit::query()->get()->first(
                fn (Unit $candidate): bool => strtoupper((string) $candidate->short_name) === $unit['short_name'],
            );

            if ($existing) {
                $existing->fill($unit + ['is_active' => true])->save();

                continue;
            }

            Unit::query()->create($unit + ['is_active' => true]);
        }
    }
}
