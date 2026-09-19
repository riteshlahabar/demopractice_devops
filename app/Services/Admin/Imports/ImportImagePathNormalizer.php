<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportImagePathContract;

final class ImportImagePathNormalizer implements ImportImagePathContract
{
    public function normalize(string $path, string $module): string
    {
        $path = ltrim(trim(str_replace('\\', '/', $path)), '/');

        if ($path === '' || str_contains($path, '..') || preg_match('/^https?:\/\//i', $path)) {
            return '';
        }

        if (str_starts_with($path, 'uploads/')) {
            return $path;
        }

        return match ($module) {
            'storefront-banners' => 'uploads/storefront/banners/home-import/'.basename($path),
            'products' => 'uploads/products/import/'.basename($path),
            default => 'uploads/imports/'.$module.'/'.basename($path),
        };
    }

    public function galleryPaths(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        return collect(preg_split('/[|;]/', $value) ?: [])
            ->map(fn ($path) => trim((string) $path))
            ->filter()
            ->map(fn ($path) => $this->normalize($path, 'products'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
