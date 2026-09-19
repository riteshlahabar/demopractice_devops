<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontFaqRepositoryContract;
use App\Models\Storefront\StorefrontFaq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentStorefrontFaqRepository implements StorefrontFaqRepositoryContract
{
    public function published(?string $category = null, ?string $search = null): Collection
    {
        $query = StorefrontFaq::query()->where('is_active', true);

        if (filled($category)) {
            $query->where('category', $category);
        }

        if (filled($search)) {
            $term = '%'.addcslashes(trim((string) $search), '%_\\').'%';

            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('question', 'like', $term)->orWhere('answer', 'like', $term);
            });
        }

        return $query->orderBy('sort_order')->orderBy('id')->get();
    }

    public function countsByCategory(): array
    {
        return StorefrontFaq::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }
}
