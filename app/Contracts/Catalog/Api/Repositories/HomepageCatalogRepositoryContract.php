<?php

namespace App\Contracts\Catalog\Api\Repositories;

use App\Models\Catalog\ProductHomepageSection;
use Illuminate\Support\Collection;

interface HomepageCatalogRepositoryContract
{
    public function activeSections(): Collection;

    public function activeCategories(): Collection;

    public function productsForSection(ProductHomepageSection $section, int $limit, string $audience): Collection;

    public function fallbackBanners(ProductHomepageSection $section): Collection;

    public function legacyBanners(): Collection;
}
