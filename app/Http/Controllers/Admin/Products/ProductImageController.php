<?php

namespace App\Http\Controllers\Admin\Products;

use App\Contracts\Catalog\Product\ProductImageContract;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** SRP: HTTP endpoints for product image deletion only. */
final class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageContract $images) {}

    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        $this->images->destroyGalleryImage($product, $image);

        return response()->json(['message' => 'Image deleted permanently.']);
    }

    public function destroyField(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate(['field' => ['required', 'string']]);
        $field = $validated['field'];
        $allowed = collect(config('admin.modules.products.fields', []))
            ->filter(fn (array $config): bool => in_array($config['type'] ?? '', ['file', 'image'], true))
            ->pluck('name')->filter()->reject(fn (string $name): bool => $name === 'primary_image')->values()->all();

        abort_unless(in_array($field, $allowed, true), 422, 'This image field cannot be deleted.');
        $this->images->destroyFieldImage($product, $field);

        return response()->json(['message' => 'Image deleted permanently.']);
    }
}
