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
            @if($storeLastOrder)
                @php
                    $orderMessage = session('success') ?: 'Your order has been placed successfully and is now moving through the live ecommerce flow.';
                    $statusLabel = ucwords(str_replace('_', ' ', (string) ($storeLastOrder->status ?: 'pending')));
                    $paymentMethodLabel = ucwords(str_replace('_', ' ', (string) ($storeLastOrder->payment_method ?: 'cod')));
                    $paymentStatusLabel = ucwords(str_replace('_', ' ', (string) ($storeLastOrder->payment_status ?: 'pending')));
                    $statusClass = in_array((string) $storeLastOrder->status, ['approved', 'packing', 'dispatched', 'delivered', 'completed'], true) ? 'is-success' : (in_array((string) $storeLastOrder->status, ['cancelled', 'rejected'], true) ? 'is-danger' : 'is-warning');
                    $paymentClass = in_array((string) $storeLastOrder->payment_status, ['paid', 'confirmed'], true) ? 'is-success' : (in_array((string) $storeLastOrder->payment_status, ['failed', 'rejected'], true) ? 'is-danger' : 'is-warning');
                @endphp
                <div class="row justify-content-center">
                    <div class="col-xxl-8 col-lg-10">
                        <div class="order-success store-order-success-hero">
                            <div class="order-image-contain store-order-success-card">
                                <div class="order-image">
                                    <div class="checkmark">
                                        <svg class="star" height="19" viewBox="0 0 19 19" width="19" xmlns="http://www.w3.org/2000/svg"><path d="M8.296.747c.532-.972 1.393-.973 1.925 0l2.665 4.872 4.876 2.66c.974.532.975 1.393 0 1.926l-4.875 2.666-2.664 4.876c-.53.972-1.39.973-1.924 0l-2.664-4.876L.76 10.206c-.972-.532-.973-1.393 0-1.925l4.872-2.66L8.296.746z"></path></svg>
                                        <svg class="star" height="19" viewBox="0 0 19 19" width="19" xmlns="http://www.w3.org/2000/svg"><path d="M8.296.747c.532-.972 1.393-.973 1.925 0l2.665 4.872 4.876 2.66c.974.532.975 1.393 0 1.926l-4.875 2.666-2.664 4.876c-.53.972-1.39.973-1.924 0l-2.664-4.876L.76 10.206c-.972-.532-.973-1.393 0-1.925l4.872-2.66L8.296.746z"></path></svg>
                                        <svg class="star" height="19" viewBox="0 0 19 19" width="19" xmlns="http://www.w3.org/2000/svg"><path d="M8.296.747c.532-.972 1.393-.973 1.925 0l2.665 4.872 4.876 2.66c.974.532.975 1.393 0 1.926l-4.875 2.666-2.664 4.876c-.53.972-1.39.973-1.924 0l-2.664-4.876L.76 10.206c-.972-.532-.973-1.393 0-1.925l4.872-2.66L8.296.746z"></path></svg>
                                        <svg class="star" height="19" viewBox="0 0 19 19" width="19" xmlns="http://www.w3.org/2000/svg"><path d="M8.296.747c.532-.972 1.393-.973 1.925 0l2.665 4.872 4.876 2.66c.974.532.975 1.393 0 1.926l-4.875 2.666-2.664 4.876c-.53.972-1.39.973-1.924 0l-2.664-4.876L.76 10.206c-.972-.532-.973-1.393 0-1.925l4.872-2.66L8.296.746z"></path></svg>
                                        <svg class="star" height="19" viewBox="0 0 19 19" width="19" xmlns="http://www.w3.org/2000/svg"><path d="M8.296.747c.532-.972 1.393-.973 1.925 0l2.665 4.872 4.876 2.66c.974.532.975 1.393 0 1.926l-4.875 2.666-2.664 4.876c-.53.972-1.39.973-1.924 0l-2.664-4.876L.76 10.206c-.972-.532-.973-1.393 0-1.925l4.872-2.66L8.296.746z"></path></svg>
                                        <svg class="checkmark__check" height="36" viewBox="0 0 48 36" width="48" xmlns="http://www.w3.org/2000/svg"><path d="M47.248 3.9L43.906.667a2.428 2.428 0 0 0-3.344 0l-23.63 23.09-9.554-9.338a2.432 2.432 0 0 0-3.345 0L.692 17.654a2.236 2.236 0 0 0 .002 3.233l14.567 14.175c.926.894 2.42.894 3.342.01L47.248 7.128c.922-.89.922-2.34 0-3.23"></path></svg>
                                        <svg class="checkmark__background" height="115" viewBox="0 0 120 115" width="120" xmlns="http://www.w3.org/2000/svg"><path d="M107.332 72.938c-1.798 5.557 4.564 15.334 1.21 19.96-3.387 4.674-14.646 1.605-19.298 5.003-4.61 3.368-5.163 15.074-10.695 16.878-5.344 1.743-12.628-7.35-18.545-7.35-5.922 0-13.206 9.088-18.543 7.345-5.538-1.804-6.09-13.515-10.696-16.877-4.657-3.398-15.91-.334-19.297-5.002-3.356-4.627 3.006-14.404 1.208-19.962C10.93 67.576 0 63.442 0 57.5c0-5.943 10.93-10.076 12.668-15.438 1.798-5.557-4.564-15.334-1.21-19.96 3.387-4.674 14.646-1.605 19.298-5.003C35.366 13.73 35.92 2.025 41.45.22c5.344-1.743 12.628 7.35 18.545 7.35 5.922 0 13.206-9.088 18.543-7.345 5.538 1.804 6.09 13.515 10.696 16.877 4.657 3.398 15.91.334 19.297 5.002 3.356 4.627-3.006 14.404-1.208 19.962C109.07 47.424 120 51.562 120 57.5c0 5.943-10.93 10.076-12.668 15.438z"></path></svg>
                                    </div>
                                </div>
                                <div class="order-contain">
                                    <span class="store-order-pill is-success mb-3">Order Confirmed</span>
                                    <h3 class="theme-color mb-2">Thank you, your order is placed successfully.</h3>
                                    <p class="text-content store-order-success-message mb-3">{{ $orderMessage }}</p>
                                    <div class="store-order-hero-meta justify-content-center">
                                        <div><span>Order No</span><strong>{{ $storeLastOrder->order_no }}</strong></div>
                                        <div><span>Placed On</span><strong>{{ $storeLastOrder->created_at?->format('d M Y, h:i A') ?: 'N/A' }}</strong></div>
                                        <div><span>Payment Mode</span><strong>{{ $paymentMethodLabel }}</strong></div>
                                    </div>
                                    <div class="store-order-status-row mt-3">
                                        <span class="store-order-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                                        <span class="store-order-pill {{ $paymentClass }}">{{ $paymentStatusLabel }}</span>
                                    </div>
                                    <div class="store-order-hero-actions justify-content-center mt-4">
                                        <a href="{{ route('store.page', ['page' => 'order-tracking', 'order' => $storeLastOrder->order_no]) }}" class="btn theme-bg-color text-white">Track Order</a>
                                        <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}" class="btn btn-md cart-button">Continue Shopping</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row"><div class="col-12"><div class="alert alert-light mb-0">No recent storefront order found. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Continue shopping</a>.</div></div></div>
            @endif
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Cart Section Start -->
    <section class="cart-section section-b-space">
        <div class="container-fluid-lg">
            @if($storeLastOrder)
                <div class="row g-sm-4 g-3">
                    <div class="col-xxl-9 col-lg-8">
                        <div class="cart-table order-table order-table-2">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        @foreach($storeLastOrder->items as $item)
                                            @php $product = $item->product; $imageUrl = optional($product?->images?->first())->url ?: asset('fastkart-store/images/vegetable/product/1.png'); $shownQuantity = $item->pack_quantity ?? $item->quantity; $quantityUnit = $storeLastOrder->order_type === 'dealer' && $item->variant_name ? 'case(s)' : 'retail pack(s)'; @endphp
                                            <tr>
                                                <td class="product-detail"><div class="product border-0"><a href="{{ $product ? route('store.product', ['product' => $product->id]) : route('store.page', ['page' => 'shop-left-sidebar']) }}" class="product-image"><img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="img-fluid blur-up lazyload" alt="{{ $product?->translatedName() ?: 'Order item' }}"></a><div class="product-detail"><ul><li class="name"><a href="{{ $product ? route('store.product', ['product' => $product->id]) : route('store.page', ['page' => 'shop-left-sidebar']) }}">{{ $product?->translatedName() ?: 'Product removed' }}@if($item->variant_name) - {{ $item->variant_name }}@endif</a></li><li class="text-content">SKU: {{ $item->variant?->variant_sku ?: ($product?->sku ?: 'Not available') }}</li><li class="text-content">Quantity - {{ rtrim(rtrim(number_format((float) $shownQuantity, 3, '.', ''), '0'), '.') }} {{ $quantityUnit }}@if($storeLastOrder->order_type === 'dealer' && $item->variant_name) ({{ rtrim(rtrim(number_format((float) $item->units_per_case, 3, '.', ''), '0'), '.') }} retail packs per case)@endif</li></ul></div></div></td>
                                                <td class="price"><h4 class="table-title text-content">Price</h4><h6 class="theme-color">Rs. {{ number_format((float) $item->unit_price, 2) }}</h6></td>
                                                <td class="quantity"><h4 class="table-title text-content">Qty</h4><h4 class="text-title">{{ rtrim(rtrim(number_format((float) $shownQuantity, 3, '.', ''), '0'), '.') }}</h4></td>
                                                <td class="subtotal"><h4 class="table-title text-content">Total</h4><h5>Rs. {{ number_format((float) $item->line_total, 2) }}</h5></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4">
                        <div class="row g-4">
                            <div class="col-lg-12 col-sm-6"><div class="summery-box"><div class="summery-header"><h3>Price Details</h3><h5 class="ms-auto theme-color">({{ $storeLastOrder->items->count() }} Items)</h5></div><ul class="summery-contain"><li><h4>Subtotal</h4><h4 class="price">Rs. {{ number_format((float) $storeLastOrder->subtotal, 2) }}</h4></li><li><h4>GST Total</h4><h4 class="price theme-color">Rs. {{ number_format((float) $storeLastOrder->gst_total, 2) }}</h4></li><li><h4>Discount</h4><h4 class="price text-danger">Rs. {{ number_format((float) $storeLastOrder->discount_total, 2) }}</h4></li></ul><ul class="summery-total"><li class="list-total"><h4>Total (INR)</h4><h4 class="price">Rs. {{ number_format((float) $storeLastOrder->grand_total, 2) }}</h4></li></ul></div></div>
                            <div class="col-lg-12 col-sm-6"><div class="summery-box"><div class="summery-header d-block"><h3>Shipping Address</h3></div><ul class="summery-contain pb-0 border-bottom-0"><li class="d-block"><h4>{{ $storeLastOrder->contact_name }}</h4><h4 class="mt-2">{{ $storeLastOrder->address_line1 }}{{ $storeLastOrder->address_line2 ? ', '.$storeLastOrder->address_line2 : '' }}</h4><h4 class="mt-2">{{ $storeLastOrder->city }}, {{ $storeLastOrder->state }} - {{ $storeLastOrder->pincode }}</h4><h4 class="mt-2">{{ $storeLastOrder->contact_mobile }}</h4></li><li class="pb-0"><h4>Order Tracking:</h4><h4 class="price theme-color"><a href="{{ route('store.page', ['page' => 'order-tracking', 'order' => $storeLastOrder->order_no]) }}" class="text-danger">Track Order</a></h4></li></ul><ul class="summery-total"><li class="list-total border-top-0 pt-2"><h4 class="fw-bold">{{ ucfirst($storeLastOrder->address_type ?: 'shipping') }}</h4></li></ul></div></div>
                            <div class="col-12"><div class="summery-box"><div class="summery-header d-block"><h3>Payment Method</h3></div><ul class="summery-contain pb-0 border-bottom-0"><li class="d-block pt-0"><p class="text-content mb-2">Payment Mode: <span class="text-title">{{ ucwords(str_replace('_', ' ', (string) ($storeLastOrder->payment_method ?: 'cod'))) }}</span></p><p class="text-content mb-2">Payment Status: <span class="text-title">{{ ucwords(str_replace('_', ' ', (string) ($storeLastOrder->payment_status ?: 'pending'))) }}</span></p><p class="text-content mb-0">Order Status: <span class="text-title">{{ ucwords(str_replace('_', ' ', (string) ($storeLastOrder->status ?: 'pending'))) }}</span></p>@if($storeLastOrder->notes)<p class="text-content mt-3 mb-0">Order Notes: {{ $storeLastOrder->notes }}</p>@endif</li></ul></div></div>
                        </div>
                    </div>
                </div>
            @endif
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
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>

    <!-- feather icon js-->
    <script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Slick js-->
    <script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>


