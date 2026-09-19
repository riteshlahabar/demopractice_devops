<?php

namespace App\Providers;

use App\Contracts\Storefront\Repositories\DeliveryAreaRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontAboutRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontCatalogRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontFaqRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontHomepageRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontLanguageRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontNavigationRepositoryContract;
use App\Contracts\Storefront\Repositories\StorefrontOrderRepositoryContract;
use App\Contracts\Storefront\Session\Repositories\StorefrontSessionProductRepositoryContract;
use App\Contracts\Storefront\Session\Repositories\StorefrontSessionUserRepositoryContract;
use App\Contracts\Storefront\Session\StorefrontCartContract;
use App\Contracts\Storefront\Session\StorefrontCartStorageContract;
use App\Contracts\Storefront\Session\StorefrontCartSummaryContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontOrderSessionContract;
use App\Contracts\Storefront\Session\StorefrontSessionProductRulesContract;
use App\Contracts\Storefront\Session\StorefrontWishlistContract;
use App\Contracts\Storefront\StorefrontAboutPageContract;
use App\Contracts\Storefront\StorefrontCatalogContract;
use App\Contracts\Storefront\StorefrontContactContract;
use App\Contracts\Storefront\StorefrontDeliveryLocationContract;
use App\Contracts\Storefront\StorefrontFaqContract;
use App\Contracts\Storefront\StorefrontHomepageContract;
use App\Contracts\Storefront\StorefrontLanguageContract;
use App\Contracts\Storefront\StorefrontNavigationContract;
use App\Contracts\Storefront\StorefrontOrderContextContract;
use App\Contracts\Storefront\StorefrontPageRendererContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Repositories\Storefront\EloquentDeliveryAreaRepository;
use App\Repositories\Storefront\EloquentStorefrontAboutRepository;
use App\Repositories\Storefront\EloquentStorefrontCatalogRepository;
use App\Repositories\Storefront\EloquentStorefrontFaqRepository;
use App\Repositories\Storefront\EloquentStorefrontHomepageRepository;
use App\Repositories\Storefront\EloquentStorefrontLanguageRepository;
use App\Repositories\Storefront\EloquentStorefrontNavigationRepository;
use App\Repositories\Storefront\EloquentStorefrontOrderRepository;
use App\Repositories\Storefront\Session\EloquentStorefrontSessionProductRepository;
use App\Repositories\Storefront\Session\EloquentStorefrontSessionUserRepository;
use App\Services\Storefront\Session\StorefrontCartService;
use App\Services\Storefront\Session\StorefrontCartStorageService;
use App\Services\Storefront\Session\StorefrontCartSummaryService;
use App\Services\Storefront\Session\StorefrontIdentitySessionService;
use App\Services\Storefront\Session\StorefrontOrderSessionService;
use App\Services\Storefront\Session\StorefrontSessionProductRules;
use App\Services\Storefront\Session\StorefrontSessionService;
use App\Services\Storefront\Session\StorefrontWishlistService;
use App\Services\Storefront\StorefrontAboutPageService;
use App\Services\Storefront\StorefrontCatalogService;
use App\Services\Storefront\StorefrontContactService;
use App\Services\Storefront\StorefrontDeliveryLocationService;
use App\Services\Storefront\StorefrontFaqService;
use App\Services\Storefront\StorefrontHomepageService;
use App\Services\Storefront\StorefrontLanguageService;
use App\Services\Storefront\StorefrontNavigationService;
use App\Services\Storefront\StorefrontOrderContextService;
use App\Services\Storefront\StorefrontPageRenderer;
use Illuminate\Support\ServiceProvider;

final class StorefrontServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $bindings = [
            StorefrontSessionContextContract::class => StorefrontSessionService::class,
            StorefrontIdentitySessionContract::class => StorefrontIdentitySessionService::class,
            StorefrontCartContract::class => StorefrontCartService::class,
            StorefrontCartStorageContract::class => StorefrontCartStorageService::class,
            StorefrontCartSummaryContract::class => StorefrontCartSummaryService::class,
            StorefrontWishlistContract::class => StorefrontWishlistService::class,
            StorefrontOrderSessionContract::class => StorefrontOrderSessionService::class,
            StorefrontSessionProductRulesContract::class => StorefrontSessionProductRules::class,
            StorefrontSessionUserRepositoryContract::class => EloquentStorefrontSessionUserRepository::class,
            StorefrontSessionProductRepositoryContract::class => EloquentStorefrontSessionProductRepository::class,
            StorefrontCatalogContract::class => StorefrontCatalogService::class,
            StorefrontCatalogRepositoryContract::class => EloquentStorefrontCatalogRepository::class,
            StorefrontHomepageContract::class => StorefrontHomepageService::class,
            StorefrontHomepageRepositoryContract::class => EloquentStorefrontHomepageRepository::class,
            StorefrontNavigationContract::class => StorefrontNavigationService::class,
            StorefrontNavigationRepositoryContract::class => EloquentStorefrontNavigationRepository::class,
            StorefrontLanguageContract::class => StorefrontLanguageService::class,
            StorefrontLanguageRepositoryContract::class => EloquentStorefrontLanguageRepository::class,
            StorefrontOrderContextContract::class => StorefrontOrderContextService::class,
            StorefrontOrderRepositoryContract::class => EloquentStorefrontOrderRepository::class,
            StorefrontDeliveryLocationContract::class => StorefrontDeliveryLocationService::class,
            DeliveryAreaRepositoryContract::class => EloquentDeliveryAreaRepository::class,
            StorefrontAboutPageContract::class => StorefrontAboutPageService::class,
            StorefrontContactContract::class => StorefrontContactService::class,
            StorefrontAboutRepositoryContract::class => EloquentStorefrontAboutRepository::class,
            StorefrontFaqContract::class => StorefrontFaqService::class,
            StorefrontFaqRepositoryContract::class => EloquentStorefrontFaqRepository::class,
            StorefrontPageRendererContract::class => StorefrontPageRenderer::class,
        ];

        foreach ($bindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
