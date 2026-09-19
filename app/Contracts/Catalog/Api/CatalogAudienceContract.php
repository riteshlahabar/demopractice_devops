<?php

namespace App\Contracts\Catalog\Api;

use App\Data\Catalog\Api\CatalogAudienceDecision;
use App\Models\User;

interface CatalogAudienceContract
{
    public function forHomepage(?User $user, string $requestedAudience): CatalogAudienceDecision;

    public function forProducts(?User $user, string $requestedAudience): CatalogAudienceDecision;
}
