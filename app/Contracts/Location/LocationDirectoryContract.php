<?php

namespace App\Contracts\Location;

/**
 * Read access to the LGD state → district → sub-district (taluka) directory.
 * Every list is [code => display name], sorted by name.
 */
interface LocationDirectoryContract
{
    /**
     * @return array<int, string>
     */
    public function states(): array;

    /**
     * @return array<int, string>
     */
    public function districts(int $stateCode): array;

    /**
     * @return array<string, string>
     */
    public function subdistricts(int $districtCode): array;
}
