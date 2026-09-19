<?php

namespace App\Repositories\Catalog\Api;

use App\Contracts\Catalog\Api\Repositories\ProductCatalogRepositoryContract;
use App\Data\Catalog\Api\ProductCatalogFilters;
use App\Models\Catalog\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentProductCatalogRepository implements ProductCatalogRepositoryContract
{
    public function paginate(ProductCatalogFilters $filters): LengthAwarePaginator
    {
        return Product::query()
            ->with([
                'category.translations',
                'brand',
                'unit',
                'images',
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with('inventoryBatches'),
                'media' => fn ($query) => $query->where('is_active', true),
            ])
            ->visibleFor($filters->audience)
            ->when(
                $filters->categoryId,
                fn ($query) => $query->where('category_id', $filters->categoryId)
            )
            ->when($filters->search !== '', function ($query) use ($filters): void {
                $query->where(function ($searchQuery) use ($filters): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$filters->search.'%')
                        ->orWhere('sku', 'like', '%'.$filters->search.'%');
                });
            })
            ->storefrontOrder()
            ->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }
}
