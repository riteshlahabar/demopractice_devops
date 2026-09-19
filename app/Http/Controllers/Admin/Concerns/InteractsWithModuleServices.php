<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * The seam between an admin module controller and its services.
 *
 * These are deliberately thin and overridable: modules such as products replace
 * `rules`, `prepareData`, `persist`, `formData` or `formOptions` and call back
 * into the version here, so the behaviour they extend stays in one place.
 *
 * @property-read AdminModuleServices $modules
 */
trait InteractsWithModuleServices
{
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100, 500];

    private const CACHE_BUSTING_MODULES = [
        'products', 'product-related-products', 'pricing', 'categories', 'brands', 'units',
        'inventory', 'warehouses', 'homepage-settings', 'homepage-setting-items', 'languages',
        'translations', 'storefront-banners', 'storefront-sections', 'storefront-section-products',
        'storefront-service-blocks', 'storefront-footer-links',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function module(): array
    {
        return $this->modules->definition->forKey($this->moduleKey);
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, array<int, mixed>>
     */
    protected function rules(array $module, ?Model $record = null): array
    {
        return $this->modules->validation->rules($module, $record);
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    protected function validateRequest(Request $request, array $module, ?Model $record = null): array
    {
        return $request->validate(
            $this->rules($module, $record),
            $this->modules->validation->messages($module),
            $this->modules->validation->attributes($module),
        );
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    protected function createFormData(Request $request, array $module): array
    {
        return $this->modules->formData->forCreate($request, $module);
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    protected function formData(Model $record, array $module): array
    {
        return $this->modules->formData->forRecord($record, $module);
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, array<mixed>>
     */
    protected function formOptions(array $module): array
    {
        return $this->modules->formData->options($module);
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, array<mixed>>
     */
    protected function formOptionAttributes(array $module): array
    {
        return $this->modules->formData->optionAttributes($module);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    protected function prepareData(array $validated, Request $request, array $module): array
    {
        return $this->modules->input->prepare($validated, $request, $module);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function persist(array $data, ?Model $record): Model
    {
        if ($record) {
            $record->fill($data)->save();
            $this->bumpCacheVersionForModule();

            return $record->fresh();
        }

        $model = $this->module()['model'];
        $record = $model::query()->create($data);
        $this->bumpCacheVersionForModule();

        return $record;
    }

    protected function records(Request $request): LengthAwarePaginator
    {
        $module = $this->module();
        [$column, $direction] = $module['sort'] ?? ['id', 'desc'];

        $requested = (int) $request->integer('per_page', (int) ($module['per_page'] ?? 10));
        $perPage = in_array($requested, self::PER_PAGE_OPTIONS, true) ? $requested : 10;

        return $this->modules->queries->filtered($request, $module)
            ->orderBy($column, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }

    protected function findRecord(int|string $id): Model
    {
        return $this->modules->queries->base($this->module())->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    protected function recordsQuery(array $module): Builder
    {
        return $this->modules->queries->base($module);
    }

    protected function viewName(string $view): string
    {
        return $this->modules->definition->viewName($this->moduleKey, $view);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    protected function pageTitle(array $module, Request $request): string
    {
        return $this->modules->definition->pageTitle($module, $request);
    }

    /**
     * Catalog and storefront responses are cached by version, so a write to one
     * of those modules has to move it on.
     */
    protected function bumpCacheVersionForModule(): void
    {
        if (in_array($this->moduleKey, self::CACHE_BUSTING_MODULES, true)) {
            Cache::forever('catalog_cache_version', ((int) Cache::get('catalog_cache_version', 1)) + 1);
        }
    }
}
