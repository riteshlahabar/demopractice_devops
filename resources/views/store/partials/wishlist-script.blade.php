@php
    $storeCartPreviewItems = collect(data_get($storeCart ?? [], 'items', collect()))
        ->take(3)
        ->map(fn (array $item): array => [
            'id' => $item['product']->id,
            'name' => $item['product']->translatedName(),
            'product_url' => route('store.product', ['product' => $item['product']->id]),
            'image_url' => $item['product']->storefront_image_url,
            'quantity' => (float) $item['quantity'],
            'unit_price' => (float) $item['unit_price'],
            'line_total' => (float) $item['line_total'],
        ])
        ->values()
        ->all();
    $storeWishlistPreviewItems = collect(data_get($storeWishlist ?? [], 'items', collect()))
        ->take(3)
        ->map(function ($product) use ($storeAudience): array {
            $price = (float) (($storeAudience ?? 'customer') === 'dealer' ? $product->dealer_price : $product->customer_price);

            return [
                'id' => $product->id,
                'name' => $product->translatedName(),
                'product_url' => route('store.product', ['product' => $product->id]),
                'image_url' => $product->storefront_image_url,
                'price' => $price,
            ];
        })
        ->values()
        ->all();
@endphp
<script>
    window.storefrontUiConfig = {
        userAuthenticated: @json((bool) $storeUser),
        csrfToken: @json(csrf_token()),
        currentUrl: @json(request()->fullUrl()),
        loginUrl: @json(route('store.page', ['page' => 'login', 'redirect_to' => request()->fullUrl()])),
        cartUrl: @json(route('store.page', ['page' => 'cart'])),
        checkoutUrl: @json(route('store.page', ['page' => 'checkout'])),
        wishlist: {
            count: {{ (int) ($storeWishlistCount ?? 0) }},
            ids: @json(array_values(array_map('intval', $storeWishlistProductIds ?? []))),
            items: @json($storeWishlistPreviewItems),
            toggleUrl: @json(route('store.wishlist.toggle')),
            url: @json(route('store.page', ['page' => 'wishlist']))
        },
        cart: {
            count: {{ json_encode((float) ($storeCartCount ?? 0)) }},
            subtotal: {{ json_encode((float) data_get($storeCart ?? [], 'subtotal', 0)) }},
            gstTotal: {{ json_encode((float) data_get($storeCart ?? [], 'gst_total', 0)) }},
            grandTotal: {{ json_encode((float) data_get($storeCart ?? [], 'grand_total', 0)) }},
            items: @json($storeCartPreviewItems),
            addUrl: @json(route('store.cart.add'))
        }
    };
</script>
<script src="{{ asset('fastkart-store/js/bawaskar-store.js') }}"></script>
