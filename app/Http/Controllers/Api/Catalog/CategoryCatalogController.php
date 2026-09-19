<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Contracts\Catalog\Api\CategoryCatalogContract;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SRP:
 * Handles HTTP request/response only.
 */
final class CategoryCatalogController extends ApiController
{
    public function __construct(
        private readonly CategoryCatalogContract $catalog
    ) {}

    public function index(
        Request $request
    ): JsonResponse {
        $locale = $request
            ->string(
                'locale',
                'en'
            )
            ->toString();

        $audience = $request
            ->string(
                'audience',
                'customer'
            )
            ->toString() === 'dealer'
                ? 'dealer'
                : 'customer';

        return $this->success([
            'categories' => $this->catalog
                ->categories(
                    $locale,
                    $audience,
                    $request->boolean(
                        'fresh'
                    )
                ),
        ]);
    }
}
