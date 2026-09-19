<?php

namespace App\Contracts\Storefront;

use Illuminate\Http\Request;

/**
 * SRP: the districts offered in the header "Your Location" modal and the one
 * the visitor picked (kept in the session).
 */
interface StorefrontDeliveryLocationContract
{
    /**
     * @return array{areas: array<int, array{code: int, name: string, state: string, min_order: float|null}>, selected: array{code: int, name: string, state: string, min_order: float|null}|null}
     */
    public function context(Request $request): array;

    /**
     * Remembers the district when it is one of the offered areas.
     */
    public function select(Request $request, int $districtCode): bool;
}
