<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\Session\StorefrontCartContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontCartController extends Controller
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontCartContract $cart
    ) {}

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request)) {
            return $response;
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $product = Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images', 'translations', 'inventoryBatches', 'variants.inventoryBatches'])
            ->findOrFail($validated['product_id']);

        $variant = null;
        if (! empty($validated['variant_id'])) {
            $variant = $product->variants->firstWhere('id', (int) $validated['variant_id']);
            abort_unless($variant instanceof ProductVariant && $variant->is_active, 422, 'Selected size/pack is not available.');
        }

        $this->cart->add($request, $product, (float) $validated['quantity'], $variant);

        return $this->cartResponse($request, $product->translatedName().' added to cart.');
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request)) {
            return $response;
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->cart->update($request, $validated['items']);

        return $this->cartResponse($request, 'Cart updated successfully.');
    }

    public function remove(Request $request, string $lineKey): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request)) {
            return $response;
        }

        $this->cart->remove($request, $lineKey);

        return $this->cartResponse($request, 'Item removed from cart.');
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        if ($response = $this->guestRedirectResponse($request)) {
            return $response;
        }

        $this->cart->clear($request);

        return $this->cartResponse($request, 'Cart cleared successfully.');
    }

    private function applyStoreLocale(Request $request): void
    {
        $locale = (string) $request->session()->get('store_locale', 'en');

        if ($locale !== '') {
            app()->setLocale($locale);
        }
    }

    private function guestRedirectResponse(Request $request): JsonResponse|RedirectResponse|null
    {
        $user = $this->identity->user($request);
        if ($user && in_array($user->role, [User::ROLE_CUSTOMER, User::ROLE_DEALER], true)) {
            return null;
        }

        $redirectTo = (string) ($request->headers->get('referer') ?: route('store.page', ['page' => 'cart']));
        $loginUrl = route('store.page', ['page' => 'login', 'redirect_to' => $redirectTo]);
        $message = 'Please log in before adding products to cart.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => $message,
                'login_url' => $loginUrl,
            ], 401);
        }

        return redirect()->to($loginUrl)->with('error', $message);
    }

    private function cartResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        $summary = $this->cart->summary($request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => (float) ($summary['count'] ?? 0),
                'subtotal' => (float) ($summary['subtotal'] ?? 0),
                'gst_total' => (float) ($summary['gst_total'] ?? 0),
                'grand_total' => (float) ($summary['grand_total'] ?? 0),
                'has_issues' => (bool) ($summary['has_issues'] ?? false),
                'items' => collect($summary['items'] ?? collect())
                    ->map(function (array $item): array {
                        $product = $item['product'];
                        $variant = $item['variant'];
                        $mrp = (float) ($variant?->mrp ?? $product->mrp);
                        $unitPrice = (float) $item['unit_price'];
                        $quantity = (float) $item['quantity'];
                        $lineBase = (float) $item['line_base'];
                        $lineTotal = (float) $item['line_total'];
                        $unitQuantity = (float) $item['unit_quantity'];
                        $savings = max(0, ($mrp * $unitQuantity) - ($variant ? $lineTotal : $lineBase));

                        return [
                            'id' => $item['line_key'],
                            'line_key' => $item['line_key'],
                            'product_id' => $product->id,
                            'variant_id' => $variant?->id,
                            'name' => $product->translatedName(),
                            'product_url' => route('store.product', ['product' => $product->id]),
                            'remove_url' => route('store.cart.remove', ['lineKey' => $item['line_key']]),
                            'image_url' => $product->storefront_image_url,
                            'category_name' => data_get($product, 'category.name') ?: 'Product',
                            'unit_name' => data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs',
                            'quantity' => $quantity,
                            'quantity_label' => $item['quantity_label'],
                            'unit_quantity' => $unitQuantity,
                            'units_per_case' => (float) $item['units_per_case'],
                            'variant_name' => $variant?->display_name,
                            'unit_price' => $unitPrice,
                            'case_price' => round($unitPrice * (float) $item['units_per_case'], 2),
                            'mrp' => $mrp,
                            'line_base' => $lineBase,
                            'line_total' => $lineTotal,
                            'available_stock' => (float) $item['available_stock'],
                            'has_issue' => (bool) $item['has_issue'],
                            'savings' => $savings,
                        ];
                    })
                    ->values()
                    ->all(),
            ]);
        }

        return back()->with('success', $message);
    }
}
