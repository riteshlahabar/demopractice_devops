<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductVariantUnitContract;
use App\Models\Catalog\Unit;
use Illuminate\Support\Collection;

final class ProductVariantUnitService implements ProductVariantUnitContract
{
    /** @var Collection<int, Unit>|null */
    private ?Collection $units = null;

    public function options(): array
    {
        return $this->units()
            ->where('is_active', true)
            ->mapWithKeys(fn (Unit $unit): array => [
                $unit->id => $unit->name.' ('.strtoupper((string) $unit->short_name).')',
            ])
            ->all();
    }

    public function shortNameFor(int|string|null $unitId): ?string
    {
        if (blank($unitId)) {
            return null;
        }

        $unit = $this->units()->firstWhere('id', (int) $unitId);

        return $unit ? strtoupper((string) $unit->short_name) : null;
    }

    public function idForShortName(?string $shortName): ?int
    {
        if (blank($shortName)) {
            return null;
        }

        $needle = strtoupper(trim((string) $shortName));

        return $this->units()
            ->first(fn (Unit $unit): bool => strtoupper((string) $unit->short_name) === $needle)?->id;
    }

    /**
     * Inactive units are kept in the lookup so an existing variant pointing at
     * one still resolves its short name; only the dropdown filters them out.
     *
     * @return Collection<int, Unit>
     */
    private function units(): Collection
    {
        return $this->units ??= Unit::query()->orderBy('name')->get();
    }
}
