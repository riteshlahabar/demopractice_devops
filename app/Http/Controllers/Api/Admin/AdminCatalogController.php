<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Catalog\Product;
use App\Models\Communication\AppTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminCatalogController extends AdminApiController
{
    public function storeProduct(Request $request): JsonResponse
    {
        $this->admin($request);

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', 'max:40'],
            'hsn_code' => ['nullable', 'string', 'max:40'],
            'gst_percent' => ['nullable', 'numeric'],
            'mrp' => ['required', 'numeric'],
            'customer_price' => ['required', 'numeric'],
            'dealer_price' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_visible_to_customers' => ['boolean'],
            'is_visible_to_dealers' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $product = Product::query()->create($validated);
        $this->bumpCatalogCacheVersion();

        return $this->success(['product' => $product], 'Product created.', 201);
    }

    public function upsertTranslation(Request $request): JsonResponse
    {
        $this->admin($request);

        $validated = $request->validate([
            'group' => ['nullable', 'string', 'max:80'],
            'translation_key' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'value' => ['required', 'string'],
        ]);

        $translation = AppTranslation::query()->updateOrCreate(
            ['translation_key' => $validated['translation_key'], 'locale' => $validated['locale']],
            ['group' => $validated['group'] ?? 'app', 'value' => $validated['value'], 'is_active' => true]
        );

        $this->bumpCatalogCacheVersion();

        return $this->success(['translation' => $translation], 'Translation saved.');
    }
}
