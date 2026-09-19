<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Store">
    <meta name="keywords" content="Bawaskar Farmer Store">
    <meta name="author" content="Bawaskar Farmer Store">
    <link rel="icon" href="{{ asset('fastkart-store/images/favicon/1.png') }}" type="image/x-icon">
    <title>Bawaskar Farmer Store</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">

    <!-- bootstrap css -->
    <link id="rtl-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/vendors/bootstrap.css') }}">

    <!-- Iconly css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/bulk-style.css') }}">

    <!-- Template css -->
    <link id="color-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/bawaskar-store.css') }}?v={{ filemtime(public_path('fastkart-store/css/bawaskar-store.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <!-- Loader Start -->
    <div class="fullpage-loader">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <!-- Loader End -->

    <!-- Header Start -->
    @include('store.partials.header')
    <!-- Header End -->

    <!-- mobile fix menu start -->
    <div class="mobile-menu d-md-none d-block mobile-cart">
        <ul>
            <li class="active">
                <a href="{{ route('store.home') }}">
                    <i class="iconly-Home icli"></i>
                    <span>{{ web_t('nav.home', 'Home') }}</span>
                </a>
            </li>

            <li class="mobile-category">
                <a href="javascript:void(0)">
                    <i class="iconly-Category icli js-link"></i>
                    <span>Category</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'search']) }}" class="search-box">
                    <i class="iconly-Search icli"></i>
                    <span>{{ web_t('header.search', 'Search') }}</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                    <i class="iconly-Heart icli"></i>
                    <span>My Wish</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'cart']) }}">
                    <i class="iconly-Bag-2 icli fly-cate"></i>
                    <span>Cart</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- mobile fix menu end -->

    <!-- Breadcrumb Section Start -->
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>Cart</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Cart</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

        <!-- Cart Section Start -->
    @php($cartItems = collect(data_get($storeCart, 'items', collect())))
    <section class="cart-section section-b-space" data-store-cart-page>
        <div class="container-fluid-lg">
            <div class="row g-sm-5 g-3 mb-3 d-none" data-store-cart-message-row>
                <div class="col-12">
                    <div class="alert mb-0" data-store-cart-message></div>
                </div>
            </div>

            @if(! $storeUser)
                <div class="row g-sm-5 g-3 mb-3">
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            Please <a href="{{ route('store.page', ['page' => 'login', 'redirect_to' => route('store.page', ['page' => 'cart'])]) }}">log in</a> to add products and continue checkout.
                        </div>
                    </div>
                </div>
            @endif

            <div class="row g-sm-5 g-3 mb-3{{ data_get($storeCart, 'has_issues') ? '' : ' d-none' }}" data-store-cart-issues-row>
                <div class="col-12">
                    <div class="alert alert-warning mb-0" data-store-cart-issues>{{ data_get($storeCart, 'has_issues') ? 'Some quantities exceed available stock. Please update your cart before checkout.' : '' }}</div>
                </div>
            </div>

            @if($cartItems->isNotEmpty())
                <form method="POST" action="{{ route('store.cart.update') }}" data-store-cart-form>
                    @csrf
                    <div class="row g-sm-5 g-3" data-store-cart-content>
                        <div class="col-xxl-9">
                            <div class="cart-table">
                                <div class="table-responsive-xl">
                                    <table class="table">
                                        <tbody data-store-cart-rows>
                                            <?php foreach ($cartItems as $item): ?>
                                                <?php
                                                    $product = $item['product'];
                                                    $imageUrl = $product->storefront_image_url;
                                                    $productUrl = route('store.product', ['product' => $product->id]);
                                                    $displayName = $product->translatedName();
                                                    $variant = $item['variant'];
                                                    $mrp = (float) ($variant?->mrp ?? $product->mrp);
                                                    $unitPrice = (float) $item['unit_price'];
                                                    $quantity = (float) $item['quantity'];
                                                    $unitQuantity = (float) $item['unit_quantity'];
                                                    $lineTotal = (float) $item['line_total'];
                                                    $hasDiscount = $mrp > $unitPrice;
                                                    $savings = max(0, ($mrp * $unitQuantity) - ($variant ? $lineTotal : (float) $item['line_base']));
                                                ?>
                                                <tr class="product-box-contain" data-product-id="{{ $product->id }}">
                                                    <td class="product-detail">
                                                        <div class="product border-0">
                                                            <a href="{{ $productUrl }}" class="product-image">
                                                                <img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}">
                                                            </a>
                                                            <div class="product-detail">
                                                                <ul>
                                                                    <li class="name">
                                                                        <a href="{{ $productUrl }}">{{ $displayName }}</a>
                                                                    </li>
                                                                    <li class="text-content">
                                                                        <span class="text-title">Category:</span> {{ data_get($product, 'category.name') ?: 'Product' }}
                                                                    </li>
                                                                    <li class="text-content">
                                                                        <span class="text-title">Size / Pack:</span> {{ $variant?->display_name ?: 'Standard Pack' }}
                                                                    </li>
                                                                    <li class="text-content">
                                                                        <span class="text-title">Quantity</span> - {{ number_format($quantity, 3) }} {{ $item['quantity_label'] }}
                                                                        @if(($storeAudience ?? 'customer') === 'dealer' && $variant)
                                                                            ({{ number_format((float) $item['units_per_case'], 0) }} retail packs per case; {{ number_format($unitQuantity, 3) }} total packs)
                                                                        @endif
                                                                    </li>
                                                                    <li>
                                                                        <h5 class="text-content d-inline-block">Price :</h5>
                                                                        <span>Rs. {{ number_format($unitPrice, 2) }} per retail pack</span>
                                                                        @if(($storeAudience ?? 'customer') === 'dealer' && $variant)
                                                                            <span class="text-content"> / Rs. {{ number_format($unitPrice * (float) $item['units_per_case'], 2) }} per case</span>
                                                                        @endif
                                                                        <?php if ($hasDiscount): ?>
                                                                            <span class="text-content">Rs. {{ number_format($mrp, 2) }}</span>
                                                                        <?php endif; ?>
                                                                    </li>
                                                                    <?php if ($savings > 0): ?>
                                                                        <li>
                                                                            <h5 class="saving theme-color">Saving : Rs. {{ number_format($savings, 2) }}</h5>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <?php if ($item['has_issue']): ?>
                                                                        <li>
                                                                            <h6 class="text-danger">Available stock: {{ number_format((float) $item['available_stock'], 3) }} retail packs</h6>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <li class="quantity-price-box">
                                                                        <div class="cart_qty">
                                                                            <div class="input-group">
                                                                                <input class="form-control input-number qty-input" type="number" min="0" step="1" name="items[{{ $item['line_key'] }}]" value="{{ $quantity }}">
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <h5>Total: Rs. {{ number_format($lineTotal, 2) }}</h5>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="price">
                                                        <h4 class="table-title text-content">Price</h4>
                                                        <h5>Rs. {{ number_format($unitPrice, 2) }}</h5>
                                                        @if(($storeAudience ?? 'customer') === 'dealer' && $variant)
                                                            <h6 class="theme-color">Rs. {{ number_format($unitPrice * (float) $item['units_per_case'], 2) }} / case</h6>
                                                        @endif
                                                        <?php if ($hasDiscount): ?>
                                                            <h6 class="text-content"><del>Rs. {{ number_format($mrp, 2) }}</del></h6>
                                                        <?php endif; ?>
                                                        <?php if ($savings > 0): ?>
                                                            <h6 class="theme-color">You Save : Rs. {{ number_format($savings, 2) }}</h6>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="quantity">
                                                        <h4 class="table-title text-content">Qty</h4>
                                                        <div class="quantity-price">
                                                            <div class="cart_qty">
                                                                <div class="input-group">
                                                                    <input class="form-control input-number qty-input" type="number" min="0" step="1" name="items[{{ $item['line_key'] }}]" value="{{ $quantity }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="subtotal">
                                                        <h4 class="table-title text-content">Total</h4>
                                                        <h5>Rs. {{ number_format($lineTotal, 2) }}</h5>
                                                    </td>
                                                    <td class="save-remove">
                                                        <h4 class="table-title text-content">Action</h4>
                                                        <button type="submit" formaction="{{ route('store.cart.remove', ['lineKey' => $item['line_key']]) }}" class="remove close_button border-0 bg-transparent">Remove</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3">
                            <div class="summery-box p-sticky">
                                <div class="summery-header">
                                    <h3>Cart Total</h3>
                                </div>

                                <div class="summery-contain">
                                    <div class="coupon-cart">
                                        <h6 class="text-content mb-2">Order Summary</h6>
                                    </div>
                                    <ul>
                                        <li><h4>Items</h4><h4 class="price" data-store-cart-page-count>{{ rtrim(rtrim(number_format((float) data_get($storeCart, 'count', 0), 3, '.', ''), '0'), '.') ?: '0' }}</h4></li>
                                        <li><h4>Subtotal</h4><h4 class="price" data-store-cart-page-subtotal>Rs. {{ number_format((float) data_get($storeCart, 'subtotal', 0), 2) }}</h4></li>
                                        <li><h4>GST</h4><h4 class="price" data-store-cart-page-gst>Rs. {{ number_format((float) data_get($storeCart, 'gst_total', 0), 2) }}</h4></li>
                                        <li class="align-items-start"><h4>Shipping</h4><h4 class="price text-end">Rs. 0.00</h4></li>
                                    </ul>
                                </div>

                                <ul class="summery-total">
                                    <li class="list-total border-top-0">
                                        <h4>Total (INR)</h4>
                                        <h4 class="price theme-color" data-store-cart-page-total>Rs. {{ number_format((float) data_get($storeCart, 'grand_total', 0), 2) }}</h4>
                                    </li>
                                </ul>

                                <div class="button-group cart-button">
                                    <ul>
                                        <li><button type="submit" class="btn btn-animation proceed-btn fw-bold">Update Cart</button></li>
                                        <li><a href="{{ route('store.page', ['page' => 'checkout']) }}" class="btn btn-animation proceed-btn fw-bold" data-store-cart-checkout-link>Process To Checkout</a></li>
                                        <li><button type="submit" formaction="{{ route('store.cart.clear') }}" class="btn btn-light shopping-button text-dark">Clear Cart</button></li>
                                        <li><a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" class="btn btn-light shopping-button text-dark"><i class="fa-solid fa-arrow-left-long"></i>Return To Shopping</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            <div class="row g-sm-5 g-3{{ $cartItems->isNotEmpty() ? ' d-none' : '' }}" data-store-cart-empty>
                <div class="col-12">
                    <div class="alert alert-light mb-0">
                        Your cart is empty. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Continue shopping</a>.
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Cart Section End -->

    <!-- Footer Section Start -->
    @include('store.partials.footer')
    <!-- Footer Section End -->

    <!-- Location Modal Start -->
    @include('store.partials.location-modal')
    <!-- Location Modal End -->

    <!-- Deal Box Modal Start -->
    @include('store.partials.deal-modal')
    <!-- Deal Box Modal End -->

    <!-- Tap to top and theme setting button start -->
    <div class="theme-option">
        <div class="setting-box">
            <button class="btn setting-button">
                <i class="fa-solid fa-gear"></i>
            </button>

            <div class="theme-setting-2">
                <div class="theme-box">
                    <ul>
                        <li>
                            <div class="setting-name">
                                <h4>Color</h4>
                            </div>
                            <div class="theme-setting-button color-picker">
                                <form class="form-control">
                                    <label for="colorPick" class="form-label mb-0">Theme Color</label>
                                    <input type="color" class="form-control form-control-color" id="colorPick"
                                        value="#0da487" title="Choose your color">
                                </form>
                            </div>
                        </li>

                        <li>
                            <div class="setting-name">
                                <h4>Dark</h4>
                            </div>
                            <div class="theme-setting-button">
                                <button class="btn btn-2 outline" id="darkButton">Dark</button>
                                <button class="btn btn-2 unline" id="lightButton">Light</button>
                            </div>
                        </li>

                        <li>
                            <div class="setting-name">
                                <h4>RTL</h4>
                            </div>
                            <div class="theme-setting-button rtl">
                                <button class="btn btn-2 rtl-unline">LTR</button>
                                <button class="btn btn-2 rtl-outline">RTL</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="back-to-top">
            <a id="back-to-top" href="#">
                <i class="fas fa-chevron-up"></i>
            </a>
        </div>
    </div>
    <!-- Tap to top and theme setting button end -->

    <!-- Bg overlay Start -->
    <div class="bg-overlay"></div>
    <!-- Bg overlay End -->

    <!-- latest jquery-->
    <script src="{{ asset('fastkart-store/js/jquery-3.6.0.min.js') }}"></script>

    <!-- jquery ui-->
    <script src="{{ asset('fastkart-store/js/jquery-ui.min.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>

    <!-- feather icon js-->
    <script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Slick js-->
    <script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- Quantity js -->
    <script src="{{ asset('fastkart-store/js/quantity.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>






