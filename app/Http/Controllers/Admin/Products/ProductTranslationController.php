<?php

namespace App\Http\Controllers\Admin\Products;

use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** SRP: HTTP endpoint for product auto-translation only. */
final class ProductTranslationController extends Controller
{
    public function __construct(private readonly ProductTranslationContract $translations) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        return response()->json([
            'translations' => $this->translations->translatePayload(
                $validated['name'],
                $validated['description'] ?? null,
            ),
        ]);
    }
}
