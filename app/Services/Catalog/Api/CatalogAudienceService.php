<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogAudienceContract;
use App\Data\Catalog\Api\CatalogAudienceDecision;
use App\Models\User;

final class CatalogAudienceService implements CatalogAudienceContract
{
    public function forHomepage(?User $user, string $requestedAudience): CatalogAudienceDecision
    {
        $audience = $requestedAudience === 'dealer' ? 'dealer' : 'customer';

        if ($audience === 'customer') {
            return new CatalogAudienceDecision($audience);
        }

        $allowed = $user && in_array(
            $user->role,
            [User::ROLE_DEALER, User::ROLE_SALESMAN, User::ROLE_ADMIN],
            true
        );

        return new CatalogAudienceDecision(
            audience: $audience,
            allowed: (bool) $allowed,
            message: $allowed ? null : 'Dealer homepage requires approved dealer login.',
            status: $allowed ? 200 : ($user ? 403 : 401)
        );
    }

    public function forProducts(?User $user, string $requestedAudience): CatalogAudienceDecision
    {
        if ($requestedAudience === 'dealer') {
            $allowed = $user && in_array(
                $user->role,
                [User::ROLE_DEALER, User::ROLE_SALESMAN, User::ROLE_ADMIN],
                true
            );

            return new CatalogAudienceDecision(
                audience: 'dealer',
                allowed: (bool) $allowed,
                message: $allowed ? null : 'Dealer catalog requires approved dealer login.',
                status: $allowed ? 200 : ($user ? 403 : 401)
            );
        }

        if ($requestedAudience === 'customer') {
            return new CatalogAudienceDecision('customer');
        }

        $audience = in_array($user?->role, [User::ROLE_DEALER, User::ROLE_SALESMAN], true)
            ? 'dealer'
            : 'customer';

        return new CatalogAudienceDecision($audience);
    }
}
