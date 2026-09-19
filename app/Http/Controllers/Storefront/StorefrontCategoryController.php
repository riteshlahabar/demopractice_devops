<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontCatalogContract;
use App\Contracts\Storefront\StorefrontPageRendererContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class StorefrontCategoryController extends Controller
{
    public function __construct(
        private readonly StorefrontSessionContextContract $session,
        private readonly StorefrontCatalogContract $catalog,
        private readonly StorefrontPageRendererContract $pages
    ) {}

    public function show(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);
        $audience = $this->session->audience($request);

        return $this->pages->render($request, 'shop-left-sidebar', [
            'selectedCategory' => $category,
            'products' => $this->catalog->categoryProducts($category, $audience),
        ]);
    }
}
