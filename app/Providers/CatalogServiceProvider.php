<?php

namespace App\Providers;

use App\Contracts\Catalog\Api\CatalogAudienceContract;
use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;
use App\Contracts\Catalog\Api\CategoryCatalogContract;
use App\Contracts\Catalog\Api\HomepageCatalogContract;
use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\HomepageCatalogPresenterContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\ProductCatalogContract;
use App\Contracts\Catalog\Api\Repositories\CategoryCatalogRepositoryContract;
use App\Contracts\Catalog\Api\Repositories\HomepageCatalogRepositoryContract;
use App\Contracts\Catalog\Api\Repositories\ProductCatalogRepositoryContract;
use App\Contracts\Catalog\Api\Repositories\TranslationCatalogRepositoryContract;
use App\Contracts\Catalog\Api\TranslationCatalogContract;
use App\Presenters\Catalog\Api\CategoryCatalogPresenter;
use App\Presenters\Catalog\Api\HomepageCatalogPresenter;
use App\Presenters\Catalog\Api\ProductCatalogPresenter;
use App\Repositories\Catalog\Api\EloquentCategoryCatalogRepository;
use App\Repositories\Catalog\Api\EloquentHomepageCatalogRepository;
use App\Repositories\Catalog\Api\EloquentProductCatalogRepository;
use App\Repositories\Catalog\Api\EloquentTranslationCatalogRepository;
use App\Services\Catalog\Api\CatalogAudienceService;
use App\Services\Catalog\Api\CategoryCatalogService;
use App\Services\Catalog\Api\HomepageCatalogService;
use App\Services\Catalog\Api\LaravelCatalogCache;
use App\Services\Catalog\Api\ProductCatalogService;
use App\Services\Catalog\Api\StorefrontCatalogTextTranslator;
use App\Services\Catalog\Api\TranslationCatalogService;
use Illuminate\Support\ServiceProvider;

/**
 * Catalog module dependency registrations.
 */
final class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CatalogCacheContract::class,
            LaravelCatalogCache::class
        );

        $this->app->bind(
            CatalogAudienceContract::class,
            CatalogAudienceService::class
        );

        $this->app->bind(
            CategoryCatalogContract::class,
            CategoryCatalogService::class
        );

        $this->app->bind(
            CategoryCatalogRepositoryContract::class,
            EloquentCategoryCatalogRepository::class
        );

        $this->app->bind(
            CategoryCatalogPresenterContract::class,
            CategoryCatalogPresenter::class
        );

        $this->app->bind(
            HomepageCatalogContract::class,
            HomepageCatalogService::class
        );

        $this->app->bind(
            HomepageCatalogRepositoryContract::class,
            EloquentHomepageCatalogRepository::class
        );

        $this->app->bind(
            HomepageCatalogPresenterContract::class,
            HomepageCatalogPresenter::class
        );

        $this->app->bind(
            CatalogTextTranslatorContract::class,
            StorefrontCatalogTextTranslator::class
        );

        $this->app->bind(
            ProductCatalogContract::class,
            ProductCatalogService::class
        );

        $this->app->bind(
            ProductCatalogRepositoryContract::class,
            EloquentProductCatalogRepository::class
        );

        $this->app->bind(
            ProductCatalogPresenterContract::class,
            ProductCatalogPresenter::class
        );

        $this->app->bind(
            TranslationCatalogContract::class,
            TranslationCatalogService::class
        );

        $this->app->bind(
            TranslationCatalogRepositoryContract::class,
            EloquentTranslationCatalogRepository::class
        );
    }
}
