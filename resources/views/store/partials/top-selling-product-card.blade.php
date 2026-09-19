@php
    $imageUrl = $product->storefront_image_url;
    $displayName = $product->translatedName();
    $productUrl = route('store.product', ['product' => $product->id]);
    $audience = $storeAudience ?? 'customer';
    $mainVariant = $product->mainVariant();
    $price = $mainVariant ? $mainVariant->priceFor($audience) : (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) ($mainVariant?->mrp ?? $product->mrp);
    $wowDelay = trim((string) ($wowDelay ?? ''));
    $isInWishlist = in_array($product->id, array_map('intval', $storeWishlistProductIds ?? []), true);
@endphp

@if($wowDelay !== '')
    <div class="product-box-4 wow fadeInUp" data-wow-delay="{{ $wowDelay }}">
@else
    <div class="product-box-4 wow fadeInUp">
@endif
    <div class="product-image product-image-2">
        <a href="{{ $productUrl }}">
            <img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}">
        </a>

        <ul class="option">
            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Quick View">
                <a href="{{ $productUrl }}">
                    <i class="iconly-Show icli"></i>
                </a>
            </li>
            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                <a href="{{ route('store.page', ['page' => 'wishlist']) }}"
                   class="notifi-wishlist store-wishlist-toggle {{ $isInWishlist ? 'is-active active' : '' }}"
                   data-store-wishlist-toggle
                   data-product-id="{{ $product->id }}"
                   data-in-wishlist="{{ $isInWishlist ? '1' : '0' }}"
                   aria-pressed="{{ $isInWishlist ? 'true' : 'false' }}">
                    <i class="iconly-Heart icli"></i>
                </a>
            </li>
        </ul>
    </div>

    <div class="product-detail">
        <ul class="rating">
            <li><i data-feather="star" class="fill"></i></li>
            <li><i data-feather="star" class="fill"></i></li>
            <li><i data-feather="star" class="fill"></i></li>
            <li><i data-feather="star" class="fill"></i></li>
            <li><i data-feather="star"></i></li>
        </ul>

        <a href="{{ $productUrl }}">
            <h5 class="name text-title">{{ storefront_public_t($product->homepage_title ?: $displayName, 'product') }}</h5>
        </a>

        @if($mrp > $price)
            <h6 class="text-content mb-1"><del>Rs. {{ number_format($mrp, 2) }}</del></h6>
        @endif
        <h5 class="price theme-color mb-0">
            Rs. {{ number_format($price, 2) }}
        </h5>

        <div class="addtocart_btn">
            <form method="POST" action="{{ route('store.cart.add') }}" data-store-cart-add>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @if($mainVariant)<input type="hidden" name="variant_id" value="{{ $mainVariant->id }}">@endif
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="add-button addcart-button btn buy-button text-light">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </form>
        </div>
    </div>
</div>


