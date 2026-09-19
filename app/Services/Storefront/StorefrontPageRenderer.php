<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\StorefrontCatalogContract;
use App\Contracts\Storefront\StorefrontDeliveryLocationContract;
use App\Contracts\Storefront\StorefrontHomepageContract;
use App\Contracts\Storefront\StorefrontLanguageContract;
use App\Contracts\Storefront\StorefrontNavigationContract;
use App\Contracts\Storefront\StorefrontOrderContextContract;
use App\Contracts\Storefront\StorefrontPageRendererContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

final class StorefrontPageRenderer implements StorefrontPageRendererContract
{
    public function __construct(
        private readonly StorefrontSessionContextContract $session,
        private readonly StorefrontCatalogContract $catalog,
        private readonly StorefrontHomepageContract $homepage,
        private readonly StorefrontNavigationContract $navigation,
        private readonly StorefrontLanguageContract $languages,
        private readonly StorefrontOrderContextContract $orders,
        private readonly StorefrontDeliveryLocationContract $deliveryLocation
    ) {}

    public function render(Request $request, string $page, array $data = []): View
    {
        $storeUser = $this->session->user($request);
        $audience = $this->session->audience($request);
        $storeCart = $this->session->cartSummary($request);
        $storeWishlist = $this->session->wishlistSummary($request);
        $orderContext = $this->orders->context($request, $storeUser);
        $storePrimaryAddress = $storeUser?->addresses->firstWhere('is_default', true)
            ?: $storeUser?->addresses->first();

        try {
            $categories = $data['categories'] ?? $this->catalog->categories($audience);
            $products = $data['products'] ?? (in_array($page, ['shop-left-sidebar', 'search'], true)
                ? $this->catalog->shopProducts(
                    $audience,
                    $request->filled('product_type')
                        ? (string) $request->query('product_type')
                        : null,
                    $request->filled('search')
                        ? trim((string) $request->query('search'))
                        : null
                )
                : $this->catalog->defaultProducts($audience));
            $homeContent = $this->homepage->content($audience);
            $storefrontNavigation = $this->navigation->data($audience);
            [$storeLanguages, $currentStoreLanguage] = $this->languages->data($request);
            $companySetting = $this->catalog->companySetting();
            $deliveryLocation = $this->deliveryLocation->context($request);
        } catch (Throwable) {
            $categories = collect();
            $products = collect();
            $homeContent = $this->homepage->emptyContent();
            $storefrontNavigation = $this->navigation->emptyData();
            [$storeLanguages, $currentStoreLanguage] = $this->languages->emptyData();
            $companySetting = null;
            $deliveryLocation = ['areas' => [], 'selected' => null];
        }

        $storeLastOrder = $orderContext['lastOrder'];
        $storeTrackedOrder = $orderContext['trackedOrder'];

        return view('store.pages.'.$page, array_merge([
            'categories' => $categories,
            'products' => $products,
            'homeContent' => $homeContent,
            'companySetting' => $companySetting,
            'deliveryAreas' => $deliveryLocation['areas'],
            'selectedDeliveryArea' => $deliveryLocation['selected'],
            'storefrontNavigation' => $storefrontNavigation,
            'storeLanguages' => $storeLanguages,
            'currentStoreLanguage' => $currentStoreLanguage,
            'selectedCategory' => null,
            'selectedProductType' => $request->query('product_type'),
            'searchQuery' => $request->query('search'),
            'storeProduct' => null,
            'relatedProducts' => collect(),
            'storeUser' => $storeUser,
            'storeAudience' => $audience,
            'storeCart' => $storeCart,
            'storeCartCount' => $storeCart['count'],
            'storeWishlist' => $storeWishlist,
            'storeWishlistCount' => (int) ($storeWishlist['count'] ?? 0),
            'storeWishlistProductIds' => $storeWishlist['ids'] ?? [],
            'storeOrders' => $orderContext['orders'],
            'storePrimaryAddress' => $storePrimaryAddress,
            'storeLastOrder' => $storeLastOrder,
            'storeTrackedOrder' => $storeTrackedOrder,
            'trackedOrder' => $storeTrackedOrder,
            'activeTrackedOrder' => $storeTrackedOrder,
        ], $data));
    }
}
