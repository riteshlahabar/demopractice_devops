@php
    $wishlistItems = collect(data_get($storeWishlist ?? [], 'items', collect()));
    $audience = $storeAudience ?? 'customer';
@endphp

<section class="wishlist-section section-b-space">
    <div class="container-fluid-lg">
        <div class="row g-sm-3 g-2" data-store-wishlist-grid @if($wishlistItems->isEmpty()) style="display: none;" @endif>
            @foreach($wishlistItems as $product)
                @php
                    $imageUrl = $product->storefront_image_url;
                    $displayName = $product->translatedName();
                    $productUrl = route('store.product', ['product' => $product->id]);
                    $mainVariant = $product->mainVariant();
                    $price = $mainVariant ? $mainVariant->priceFor($audience) : (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
                    $mrp = (float) ($mainVariant?->mrp ?? $product->mrp);
                    $unitName = $mainVariant?->display_name ?: (data_get($product, 'unit.short_name') ?: data_get($product, 'unit.name') ?: 'pcs');
                    $availableStock = $mainVariant ? (float) $mainVariant->available_stock : (float) $product->available_stock;
                    $lowStockAlert = (float) optional($mainVariant?->inventoryBatches->first() ?: $product->inventoryBatches->first())->low_stock_alert;
                    $isOutOfStock = $availableStock <= 0;
                    $isLowStock = ! $isOutOfStock && $lowStockAlert > 0 && $availableStock <= $lowStockAlert;
                @endphp
                <div class="col-xxl-2 col-lg-3 col-md-4 col-6 product-box-contain" data-store-wishlist-card data-product-id="{{ $product->id }}">
                    <div class="product-box-3 h-100">
                        <div class="product-header">
                            <div class="product-image">
                                <a href="{{ $productUrl }}">
                                    <img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}">
                                </a>

                                <div class="product-header-top">
                                    <a href="{{ route('store.page', ['page' => 'wishlist']) }}"
                                       class="btn wishlist-button close_button store-wishlist-toggle is-active"
                                       data-store-wishlist-toggle
                                       data-product-id="{{ $product->id }}"
                                       data-in-wishlist="1"
                                       aria-pressed="true">
                                        <i data-feather="x"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="product-footer">
                            <div class="product-detail">
                                <span class="span-name">{{ data_get($product, 'category.storefront_name') ?: web_t('product.fallback', 'Product') }}</span>
                                <a href="{{ $productUrl }}">
                                    <h5 class="name">{{ $displayName }}</h5>
                                </a>
                                <h6 class="unit mt-1">{{ storefront_public_t($unitName, 'unit') }}</h6>
                                <h5 class="price">
                                    <span class="theme-color">Rs. {{ number_format($price, 2) }}</span>
                                    @if($mrp > $price)
                                        <del>Rs. {{ number_format($mrp, 2) }}</del>
                                    @endif
                                </h5>

                                @if($isOutOfStock)
                                    <div class="add-to-cart-box bg-white mt-2">
                                        <button class="btn btn-add-cart addcart-button" disabled>{{ web_t('product.out_of_stock', 'Out of Stock') }}</button>
                                    </div>
                                @else
                                    <div class="add-to-cart-box bg-white mt-2">
                                        <form method="POST" action="{{ route('store.cart.add') }}" data-store-cart-add>
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            @if($mainVariant)<input type="hidden" name="variant_id" value="{{ $mainVariant->id }}">@endif
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if($isLowStock)
                                    <h6 class="theme-color mt-2">{{ storefront_public_t($product->low_stock_text ?: web_t('product.low_stock', 'Low Stock'), 'product') }}</h6>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row @if($wishlistItems->isNotEmpty()) d-none @endif" data-store-wishlist-empty>
            <div class="col-12">
                <div class="dashboard-bg-box text-center py-5 px-4">
                    <h3 class="mb-2">Your wishlist is empty</h3>
                    <p class="text-content mb-3">Save products here and come back to them anytime.</p>
                    <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" class="btn theme-bg-color text-white">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</section>


