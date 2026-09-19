<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontCatalogContract;
use App\Contracts\Storefront\StorefrontPageRendererContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class StorefrontProductController extends Controller
{
    public function __construct(
        private readonly StorefrontSessionContextContract $session,
        private readonly StorefrontCatalogContract $catalog,
        private readonly StorefrontPageRendererContract $pages
    ) {}

    public function show(Request $request, Product $product): View
    {
        $audience = $this->session->audience($request);
        $visibleColumn = $audience === 'dealer'
            ? 'is_visible_to_dealers'
            : 'is_visible_to_customers';

        abort_unless($product->is_active && (bool) $product->{$visibleColumn}, 404);

        return $this->pages->render(
            $request,
            'product-left-thumbnail',
            $this->catalog->productDetails($product, $audience)
        );
    }
}
