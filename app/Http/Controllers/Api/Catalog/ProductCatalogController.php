<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Contracts\Catalog\Api\CatalogAudienceContract;
use App\Contracts\Catalog\Api\ProductCatalogContract;
use App\Data\Catalog\Api\ProductCatalogFilters;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductCatalogController extends ApiController
{
    public function __construct(
        private readonly ProductCatalogContract $catalog,
        private readonly CatalogAudienceContract $audiences
    ) {}

    public function index(Request $request): JsonResponse
    {
        $requestedAudience = $request->string('audience', 'customer')->toString();
        $decision = $this->audiences->forProducts($this->user($request), $requestedAudience);

        if (! $decision->allowed) {
            return $this->fail((string) $decision->message, $decision->status);
        }

        $filters = new ProductCatalogFilters(
            audience: $decision->audience,
            categoryId: $request->integer('category_id') ?: null,
            search: trim($request->string('search')->toString()),
            page: max(1, $request->integer('page', 1)),
            perPage: min(max(1, $request->integer('per_page', 20)), 100)
        );

        return $this->success([
            'products' => $this->catalog->products($filters, $request->boolean('fresh')),
            'price_type' => $decision->audience === 'dealer' ? 'dealer_price' : 'customer_price',
        ]);
    }
}
