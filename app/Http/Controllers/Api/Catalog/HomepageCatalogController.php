<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Contracts\Catalog\Api\CatalogAudienceContract;
use App\Contracts\Catalog\Api\HomepageCatalogContract;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HomepageCatalogController extends ApiController
{
    public function __construct(
        private readonly HomepageCatalogContract $catalog,
        private readonly CatalogAudienceContract $audiences
    ) {}

    public function index(Request $request): JsonResponse
    {
        $requestedAudience = $request->string('audience', 'customer')->toString();
        $user = $requestedAudience === 'dealer' ? $this->user($request) : null;
        $decision = $this->audiences->forHomepage($user, $requestedAudience);

        if (! $decision->allowed) {
            return $this->fail((string) $decision->message, $decision->status);
        }

        return $this->success($this->catalog->homepage($decision->audience));
    }
}
