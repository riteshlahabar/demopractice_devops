<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Session\StorefrontCartContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontOrderSessionContract;
use App\Contracts\Storefront\Session\StorefrontWishlistContract;
use App\Http\Controllers\Storefront\StorefrontAuthController;
use App\Http\Controllers\Storefront\StorefrontCartController;
use App\Http\Controllers\Storefront\StorefrontCategoryController;
use App\Http\Controllers\Storefront\StorefrontCheckoutController;
use App\Http\Controllers\Storefront\StorefrontLanguageController;
use App\Http\Controllers\Storefront\StorefrontPageController;
use App\Http\Controllers\Storefront\StorefrontPreviewController;
use App\Http\Controllers\Storefront\StorefrontProductController;
use App\Http\Controllers\Storefront\StorefrontWishlistController;
use Tests\TestCase;

class StorefrontServiceProviderTest extends TestCase
{
    public function test_all_refactored_storefront_controllers_resolve_from_the_container(): void
    {
        $controllers = [
            StorefrontPageController::class,
            StorefrontCategoryController::class,
            StorefrontProductController::class,
            StorefrontLanguageController::class,
            StorefrontPreviewController::class,
            StorefrontAuthController::class,
            StorefrontCartController::class,
            StorefrontCheckoutController::class,
            StorefrontWishlistController::class,
        ];

        foreach ($controllers as $controller) {
            $this->assertInstanceOf($controller, $this->app->make($controller));
        }
    }

    public function test_focused_storefront_session_contracts_resolve_from_the_container(): void
    {
        $contracts = [
            StorefrontIdentitySessionContract::class,
            StorefrontCartContract::class,
            StorefrontWishlistContract::class,
            StorefrontOrderSessionContract::class,
        ];

        foreach ($contracts as $contract) {
            $this->assertInstanceOf($contract, $this->app->make($contract));
        }
    }
}
