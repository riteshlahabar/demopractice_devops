<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportImagePathContract;
use App\Contracts\Admin\Imports\ImportRelationResolverContract;
use App\Contracts\Admin\Imports\ImportRowMapperContract;
use App\Contracts\Admin\Imports\ImportRowReaderContract;
use App\Contracts\Admin\Imports\ImportRunnerContract;
use App\Contracts\Admin\Imports\ProductImportMastersContract;
use App\Contracts\Admin\Imports\ProductImportMediaContract;
use App\Data\Admin\ImportResult;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class ModuleImportRunner implements ImportRunnerContract
{
    private const CACHE_BUSTING_MODULES = ['products', 'categories', 'brands', 'units', 'inventory', 'batches'];

    public function __construct(
        private readonly ImportRowReaderContract $reader,
        private readonly ImportRowMapperContract $mapper,
        private readonly ImportRelationResolverContract $relations,
        private readonly ImportImagePathContract $paths,
        private readonly ProductImportMastersContract $masters,
        private readonly ProductImportMediaContract $media,
    ) {}

    public function run(string $module, array $moduleConfig, array $headers, array $rows, array $forcedValues = []): ImportResult
    {
        $model = $moduleConfig['model'];
        $fields = collect($moduleConfig['fields'] ?? [])->pluck('name')->filter()->values()->all();

        $created = 0;
        $updated = 0;
        $failed = 0;
        $firstError = null;

        foreach ($rows as $index => $values) {
            $row = $this->combine($headers, $values);

            if ($row === null) {
                continue;
            }

            try {
                $wasCreated = $this->importRow($module, $model, $fields, array_merge($row, $forcedValues));
                $wasCreated ? $created++ : $updated++;
            } catch (Throwable $e) {
                $failed++;
                $firstError ??= 'Line '.($index + 2).': '.$e->getMessage();
            }
        }

        $this->bumpCacheVersion($module);

        return new ImportResult($created, $updated, $failed, $firstError);
    }

    /**
     * @return array<string, mixed>|null Null for a blank line.
     */
    private function combine(array $headers, array $values): ?array
    {
        $values = array_pad($values, count($headers), null);
        $row = array_combine($headers, array_slice($values, 0, count($headers)));

        if (! $row || count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
            return null;
        }

        return $row;
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<string, mixed>  $row
     * @return bool True when a new record was created.
     */
    private function importRow(string $module, string $model, array $fields, array $row): bool
    {
        if ($module === 'products') {
            $this->masters->ensure($row);
        }

        $data = $this->relations->resolve($this->mapper->map($row, $fields, $module), $row, $module);

        $imagePath = $this->reader->firstFilled($row, ['primary_image', 'image_path', 'product_image']);
        $galleryPaths = $this->paths->galleryPaths($this->reader->firstFilled($row, ['gallery_images', 'gallery_image', 'product_gallery']));
        unset($data['primary_image'], $data['gallery_images']);

        [$record, $created] = $this->upsert($model, $data, $this->mapper->uniqueKeysFor($data, $module));

        if ($module === 'products' && $record instanceof Product) {
            $this->syncProductMedia($record, $imagePath, $galleryPaths);
        }

        return $created;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $where
     * @return array{0: Model, 1: bool}
     */
    private function upsert(string $model, array $data, array $where): array
    {
        $existing = $where === [] ? null : $model::query()->where($where)->first();

        if ($existing) {
            $existing->fill($data)->save();

            return [$existing, false];
        }

        return [$model::query()->create($data), true];
    }

    /**
     * @param  array<int, string>  $galleryPaths
     */
    private function syncProductMedia(Product $product, ?string $imagePath, array $galleryPaths): void
    {
        if ($imagePath) {
            $this->media->syncPrimaryImage($product, $imagePath);
        }

        if ($galleryPaths !== []) {
            $this->media->syncGalleryImages($product, $galleryPaths);
        }

        $this->media->syncHomepageDisplay($product->fresh(['images']));
    }

    private function bumpCacheVersion(string $module): void
    {
        if (in_array($module, self::CACHE_BUSTING_MODULES, true)) {
            Cache::forever('catalog_cache_version', ((int) Cache::get('catalog_cache_version', 1)) + 1);
        }
    }
}
