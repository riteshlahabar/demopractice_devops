@php
    $imageUrl = $product->storefront_image_url;
    $displayName = $product->translatedName();
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $mainVariant = $product->mainVariant();
    $price = $mainVariant ? $mainVariant->priceFor($audience) : (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) ($mainVariant?->mrp ?? $product->mrp);
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $unitName = $mainVariant?->display_name ?: '1 '.(data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs');
    $availableStock = $mainVariant ? (float) $mainVariant->available_stock : (float) $product->available_stock;
    $lowStockAlert = (float) optional($mainVariant?->inventoryBatches->first() ?: $product->inventoryBatches->first())->low_stock_alert;
    $isOutOfStock = $availableStock <= 0;
    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
    $isInWishlist = in_array($product->id, array_map('intval', $storeWishlistProductIds ?? []), true);
@endphp

<div>
    <div class="product-box-3 h-100 wow fadeInUp">
        <div class="product-header">
            <div class="product-image">
                <a href="{{ $productUrl }}">
                    <img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}">
                </a>
                <ul class="product-option">
                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                        <a href="{{ $productUrl }}"><i data-feather="eye"></i></a>
                    </li>
                    <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                        <a href="{{ route('store.page', ['page' => 'wishlist']) }}"
                           class="notifi-wishlist store-wishlist-toggle {{ $isInWishlist ? 'is-active active' : '' }}"
                           data-store-wishlist-toggle
                           data-product-id="{{ $product->id }}"
                           data-in-wishlist="{{ $isInWishlist ? '1' : '0' }}"
                           aria-pressed="{{ $isInWishlist ? 'true' : 'false' }}"><i data-feather="heart"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="product-footer">
            <div class="product-detail">
                <a href="{{ $productUrl }}"><h5 class="name">{{ $displayName }}</h5></a>
                <h6 class="unit">{{ storefront_public_t($unitName, 'unit') }}</h6>
                @if($mrp > $price)
                    <h6 class="text-content mb-1"><del>Rs. {{ number_format($mrp, 2) }}</del></h6>
                @endif
                <h5 class="price mb-0">
                    <span class="theme-color">Rs. {{ number_format($price, 2) }}</span>
                </h5>
                @if($isOutOfStock)
                    <h6 class="theme-color">{{ web_t('product.out_of_stock', 'Out of Stock') }}</h6>
                @elseif($isLowStock)
                    <h6 class="theme-color">{{ storefront_public_t($product->low_stock_text ?: web_t('product.low_stock', 'Low Stock'), 'product') }}</h6>
                @elseif($discount > 0)
                    <h6 class="theme-color">{{ $discount }}% off</h6>
                @endif
                <div class="add-to-cart-box bg-white">
                    @if($isOutOfStock)
                        <button class="btn btn-add-cart addcart-button" disabled>{{ web_t('product.out_of_stock', 'Out of Stock') }}</button>
                    @else
                        <form method="POST" action="{{ route('store.cart.add') }}" data-store-cart-add>
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if($mainVariant)<input type="hidden" name="variant_id" value="{{ $mainVariant->id }}">@endif
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-add-cart addcart-button">{{ web_t('product.add_to_cart', 'Add To Cart') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


