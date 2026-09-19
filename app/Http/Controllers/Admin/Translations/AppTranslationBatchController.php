<?php

namespace App\Http\Controllers\Admin\Translations;

use App\Contracts\Localization\AppTranslationBatchContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The App Translations "Translate" button calls this repeatedly; each call
 * translates one small batch and reports how many rows are still missing.
 */
final class AppTranslationBatchController extends Controller
{
    public function __construct(private readonly AppTranslationBatchContract $batches) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app' => ['nullable', 'string', Rule::in(array_keys((array) config('localization.apps', [])))],
        ]);

        return response()->json($this->batches->translateNextBatch($validated['app'] ?? null));
    }
}
