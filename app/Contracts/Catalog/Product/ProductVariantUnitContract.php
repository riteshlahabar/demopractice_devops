<?php

namespace App\Contracts\Catalog\Product;

/**
 * SRP: resolves the Unit master against the packing unit stored on a variant.
 *
 * Variants keep both `unit_id` (the Unit master record) and `size_unit` (the
 * uppercased short name). The short name is what the storefront, invoices and
 * legacy rows read, so it always has to stay in sync with the selected unit.
 */
interface ProductVariantUnitContract
{
    /**
     * Dropdown options for the variant unit select: unit id => display label.
     *
     * @return array<int, string>
     */
    public function options(): array;

    /**
     * Uppercased short name for a unit id, used to keep `size_unit` in sync.
     */
    public function shortNameFor(int|string|null $unitId): ?string;

    /**
     * Unit id matching a legacy `size_unit` short name, so existing variants
     * preselect the right option when the form is opened.
     */
    public function idForShortName(?string $shortName): ?int;
}
