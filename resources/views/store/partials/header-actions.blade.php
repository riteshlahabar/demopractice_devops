@php
    $headerCartItems = collect(data_get($storeCart ?? [], 'items', collect()))->take(3);
    $headerCartCount = rtrim(rtrim(number_format((float) data_get($storeCart ?? [], 'count', 0), 3, '.', ''), '0'), '.');
    $headerCartCount = $headerCartCount !== '' ? $headerCartCount : '0';
    $headerCartTotal = (float) data_get($storeCart ?? [], 'grand_total', 0);
    $headerWishlistCount = (int) data_get($storeWishlist ?? [], 'count', 0);
    $headerUserRole = $storeUser?->role === 'dealer' ? 'Dealer' : 'Customer';
    $headerCompany = $companySetting ?? null;
    $headerPhone = filled($headerCompany?->phone) ? trim((string) $headerCompany->phone) : null;
@endphp

<ul class="right-side-menu">
    <li class="right-side">
        <div class="delivery-login-box">
            <div class="delivery-icon">
                <div class="search-box">
                    <i data-feather="search"></i>
                </div>
            </div>
        </div>
    </li>
    @if($headerPhone)
        <li class="right-side">
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $headerPhone) }}" class="delivery-login-box">
                <div class="delivery-icon">
                    <i data-feather="phone-call"></i>
                </div>
                <div class="delivery-detail">
                    <h6>{{ web_t('header.delivery_support', '24/7 Delivery') }}</h6>
                    <h5>{{ $headerPhone }}</h5>
                </div>
            </a>
        </li>
    @endif
    <li class="right-side">
        <a href="{{ route('store.page', ['page' => 'wishlist']) }}" class="btn p-0 position-relative header-wishlist" data-store-wishlist-link>
            <i data-feather="heart"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge store-wishlist-count {{ $headerWishlistCount > 0 ? '' : 'd-none' }}">{{ $headerWishlistCount }}<span class="visually-hidden">wishlist items</span></span>
        </a>
    </li>
    <li class="right-side">
        <div class="onhover-dropdown header-badge">
            <button type="button" class="btn p-0 position-relative header-cart-button" data-store-cart-button>
                <i data-feather="shopping-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge" data-store-cart-count-target>{{ $headerCartCount }}
                    <span class="visually-hidden">cart items</span>
                </span>
            </button>

            <div class="onhover-div">
                <ul class="cart-list" data-store-cart-list>
                    @forelse($headerCartItems as $item)
                        @php
                            $product = $item['product'];
                            $productUrl = route('store.product', ['product' => $product->id]);
                            $imageUrl = $product->storefront_image_url;
                            $displayName = $product->translatedName();
                        @endphp
                        <li class="product-box-contain">
                            <div class="drop-cart">
                                <a href="{{ $productUrl }}" class="drop-image">
                                    <img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="blur-up lazyload" alt="{{ $displayName }}">
                                </a>

                                <div class="drop-contain">
                                    <a href="{{ $productUrl }}">
                                        <h5>{{ $displayName }}</h5>
                                    </a>
                                    <h6><span>{{ rtrim(rtrim(number_format((float) $item['quantity'], 3, '.', ''), '0'), '.') }} x</span> Rs. {{ number_format((float) $item['unit_price'], 2) }}</h6>
                                    <form method="POST" action="{{ route('store.cart.remove', ['productId' => $product->id]) }}" data-store-cart-remove-form>
                                        @csrf
                                        <button type="submit" class="close-button close_button">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="product-box-contain">
                            <div class="drop-cart">
                                <div class="drop-contain">
                                    <h5>Your cart is empty.</h5>
                                    <h6>Add products to continue shopping.</h6>
                                </div>
                            </div>
                        </li>
                    @endforelse
                </ul>

                <div class="price-box">
                    <h5>Total :</h5>
                    <h4 class="theme-color fw-bold" data-store-cart-total>Rs. {{ number_format($headerCartTotal, 2) }}</h4>
                </div>

                <div class="button-group">
                    <a href="{{ route('store.page', ['page' => 'cart']) }}" class="btn btn-sm cart-button" data-store-cart-link>View Cart</a>
                    <a href="{{ route('store.page', ['page' => 'checkout']) }}" class="btn btn-sm cart-button theme-bg-color text-white" data-store-checkout-link>Checkout</a>
                </div>
            </div>
        </div>
    </li>
    <li class="right-side onhover-dropdown">
        <div class="delivery-login-box">
            <div class="delivery-icon">
                <i data-feather="user"></i>
            </div>
            <div class="delivery-detail">
                <h6>{{ $storeUser ? $headerUserRole : 'Hello,' }}</h6>
                <h5>{{ $storeUser?->name ?: 'My Account' }}</h5>
            </div>
        </div>

        <div class="onhover-div onhover-div-login">
            <ul class="user-box-name">
                @if($storeUser)
                    <li class="product-box-contain">
                        <a href="{{ route('store.page', ['page' => 'user-dashboard']) }}">{{ $headerUserRole }} Dashboard</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="{{ route('store.page', ['page' => 'user-dashboard']) }}#pills-order">Recent Order</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="{{ route('store.page', ['page' => 'order-tracking']) }}">Track Order</a>
                    </li>
                    <li class="product-box-contain">
                        <form method="POST" action="{{ route('store.auth.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-start text-decoration-none">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="product-box-contain">
                        <a href="{{ route('store.page', ['page' => 'login']) }}">Log In</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="{{ route('store.page', ['page' => 'sign-up']) }}">Register</a>
                    </li>
                    <li class="product-box-contain">
                        <a href="{{ route('store.page', ['page' => 'forgot']) }}">Forgot Password</a>
                    </li>
                @endif
            </ul>
        </div>
    </li>
</ul>


