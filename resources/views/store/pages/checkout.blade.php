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
                        <h2>Checkout</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Checkout</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

        <!-- Checkout section Start -->
    @php
        $cartItems = collect(data_get($storeCart, 'items', collect()));
        $address = $storePrimaryAddress;
    @endphp
    <section class="checkout-section-2 section-b-space">
        <div class="container-fluid-lg">
            @if(! $storeUser)
                <div class="alert alert-warning mb-0">Please <a href="{{ route('store.page', ['page' => 'login', 'redirect_to' => route('store.page', ['page' => 'checkout'])]) }}">log in</a> before checkout.</div>
            @elseif($cartItems->isEmpty())
                <div class="alert alert-light mb-0">Your cart is empty. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Browse products</a>.</div>
            @else
                <form method="POST" action="{{ route('store.checkout.place-order') }}">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger store-checkout-error-alert mb-4" role="alert">
                            <h5 class="mb-2">Please complete the required checkout details.</h5>
                            <ul class="mb-0 store-checkout-error-list">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row g-sm-4 g-3">
                        <div class="col-lg-8">
                            <div class="left-sidebar-checkout">
                                <div class="checkout-detail-box">
                                    <ul>
                                        <li>
                                            <div class="checkout-icon">
                                                <lord-icon target=".nav-item" src="https://cdn.lordicon.com/ggihhudh.json" trigger="loop-on-hover" colors="primary:#121331,secondary:#646e78,tertiary:#0baf9a" class="lord-icon"></lord-icon>
                                            </div>
                                            <div class="checkout-box">
                                                <div class="checkout-title"><h4>Delivery Address</h4></div>
                                                <div class="checkout-detail">
                                                    @if($address)
                                                        <div class="row g-4 mb-4">
                                                            <div class="col-12">
                                                                <div class="delivery-address-box">
                                                                    <div>
                                                                        <div class="form-check"><input class="form-check-input" type="radio" checked></div>
                                                                        <div class="label"><label>{{ ucfirst($address->type ?: 'shipping') }}</label></div>
                                                                        <ul class="delivery-address-detail">
                                                                            <li><h4 class="fw-500">{{ $address->name }}</h4></li>
                                                                            <li><p class="text-content"><span class="text-title">Address :</span> {{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}, {{ $address->city }}, {{ $address->state }}</p></li>
                                                                            <li><h6 class="text-content"><span class="text-title">Pin Code :</span> {{ $address->pincode }}</h6></li>
                                                                            <li><h6 class="text-content mb-0"><span class="text-title">Phone :</span> {{ $address->mobile }}</h6></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('contact_name') is-invalid @enderror" name="contact_name" id="contact_name" value="{{ old('contact_name', $address?->name ?: $storeUser->name) }}" placeholder="Contact Name">
                                                                <label for="contact_name">Contact Name</label>
                                                            </div>
                                                            @error('contact_name')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('contact_mobile') is-invalid @enderror" name="contact_mobile" id="contact_mobile" value="{{ old('contact_mobile', $address?->mobile ?: $storeUser->mobile) }}" placeholder="Contact Mobile">
                                                                <label for="contact_mobile">Contact Mobile</label>
                                                            </div>
                                                            @error('contact_mobile')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('address_type') is-invalid @enderror" name="address_type" id="address_type" value="{{ old('address_type', $address?->type ?: 'shipping') }}" placeholder="Address Type">
                                                                <label for="address_type">Address Type</label>
                                                            </div>
                                                            @error('address_type')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('address_line1') is-invalid @enderror" name="address_line1" id="address_line1" value="{{ old('address_line1', $address?->address_line1) }}" placeholder="Address Line 1">
                                                                <label for="address_line1">Address Line 1</label>
                                                            </div>
                                                            @error('address_line1')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('address_line2') is-invalid @enderror" name="address_line2" id="address_line2" value="{{ old('address_line2', $address?->address_line2) }}" placeholder="Address Line 2">
                                                                <label for="address_line2">Address Line 2</label>
                                                            </div>
                                                            @error('address_line2')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $address?->city) }}" placeholder="City">
                                                                <label for="city">City</label>
                                                            </div>
                                                            @error('city')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state', $address?->state) }}" placeholder="State">
                                                                <label for="state">State</label>
                                                            </div>
                                                            @error('state')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating theme-form-floating">
                                                                <input type="text" class="form-control @error('pincode') is-invalid @enderror" name="pincode" id="pincode" value="{{ old('pincode', $address?->pincode) }}" placeholder="Pincode">
                                                                <label for="pincode">Pincode</label>
                                                            </div>
                                                            @error('pincode')<div class="invalid-feedback d-block store-checkout-field-error">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-12"><div class="form-check custom-form-check"><input class="form-check-input" type="checkbox" name="save_as_default" id="save_as_default" value="1" {{ old('save_as_default', $address?->is_default) ? 'checked' : '' }}><label class="form-check-label" for="save_as_default">Save as default address</label></div></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="checkout-icon">
                                                <lord-icon target=".nav-item" src="https://cdn.lordicon.com/oaflahpk.json" trigger="loop-on-hover" colors="primary:#0baf9a" class="lord-icon"></lord-icon>
                                            </div>
                                            <div class="checkout-box">
                                                <div class="checkout-title"><h4>Delivery Option</h4></div>
                                                <div class="checkout-detail">
                                                    <div class="delivery-option"><div class="delivery-category"><div class="shipment-detail"><div class="form-check custom-form-check hide-check-box"><input class="form-check-input" type="radio" checked><label class="form-check-label">Standard Delivery Option</label></div></div></div></div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="checkout-icon">
                                                <lord-icon target=".nav-item" src="https://cdn.lordicon.com/qmcsqnle.json" trigger="loop-on-hover" colors="primary:#0baf9a,secondary:#0baf9a" class="lord-icon"></lord-icon>
                                            </div>
                                            <div class="checkout-box">
                                                <div class="checkout-title"><h4>Payment Option</h4></div>
                                                <div class="checkout-detail">
                                                    <div class="accordion accordion-flush custom-accordion" id="accordionFlushExample">
                                                        <div class="accordion-item">
                                                            <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="cash"><input class="form-check-input mt-0" type="radio" name="payment_method" id="cash" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}> Cash On Delivery</label></div></div></div>
                                                            <div id="flush-collapseFour" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Cash on delivery is available. Order will stay pending until dispatch confirmation.</p></div></div>
                                                        </div>
                                                        <div class="accordion-item">
                                                            <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="upi"><input class="form-check-input mt-0" type="radio" name="payment_method" id="upi" value="upi" {{ old('payment_method') === 'upi' ? 'checked' : '' }}> UPI</label></div></div></div>
                                                            <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Choose this if payment will be confirmed by UPI collection after order placement.</p></div></div>
                                                        </div>
                                                        <div class="accordion-item">
                                                            <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="bank_transfer"><input class="form-check-input mt-0" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}> Bank Transfer</label></div></div></div>
                                                            <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Use this for NEFT / RTGS / IMPS style payment confirmation after order placement.</p></div></div>
                                                        </div>
                                                        @if($storeUser?->role === \App\Models\User::ROLE_DEALER)
                                                            <div class="accordion-item">
                                                                <div class="accordion-header"><div class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree"><div class="custom-form-check form-check mb-0"><label class="form-check-label" for="credit"><input class="form-check-input mt-0" type="radio" name="payment_method" id="credit" value="credit" {{ old('payment_method') === 'credit' ? 'checked' : '' }}> Dealer Credit</label></div></div></div>
                                                                <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample"><div class="accordion-body"><p class="cod-review">Dealer credit can be used for approved dealer accounts and remains subject to account verification.</p></div></div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @error('payment_method')<div class="invalid-feedback d-block store-checkout-field-error mt-2">{{ $message }}</div>@enderror
                                                    <div class="mt-4"><div class="form-floating theme-form-floating"><textarea class="form-control" name="notes" id="notes" placeholder="Order Notes" style="height: 120px">{{ old('notes') }}</textarea><label for="notes">Order Notes</label></div></div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="right-side-summery-box">
                                <div class="summery-box-2">
                                    <div class="summery-header"><h3>Order Summery</h3></div>
                                    <ul class="summery-contain">
                                        @foreach($cartItems as $item)
                                            @php $imageUrl = $item['product']->storefront_image_url; $displayName = $item['product']->translatedName(); @endphp
                                            <li>
                                                <img loading="lazy" decoding="async" src="{{ $imageUrl }}" class="img-fluid blur-up lazyloaded checkout-image" alt="{{ $displayName }}">
                                                <h4>{{ $displayName }} <span>X {{ number_format((float) $item['quantity'], 3) }}</span></h4>
                                                <h4 class="price">Rs. {{ number_format((float) $item['line_total'], 2) }}</h4>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul class="summery-total">
                                        <li><h4>Subtotal</h4><h4 class="price">Rs. {{ number_format((float) data_get($storeCart, 'subtotal', 0), 2) }}</h4></li>
                                        <li><h4>Shipping</h4><h4 class="price">Rs. 0.00</h4></li>
                                        <li><h4>Tax</h4><h4 class="price">Rs. {{ number_format((float) data_get($storeCart, 'gst_total', 0), 2) }}</h4></li>
                                        <li><h4>Coupon/Code</h4><h4 class="price">Rs. 0.00</h4></li>
                                        <li class="list-total"><h4>Total (INR)</h4><h4 class="price">Rs. {{ number_format((float) data_get($storeCart, 'grand_total', 0), 2) }}</h4></li>
                                    </ul>
                                </div>
                                <div class="checkout-offer">
                                    <div class="offer-title">
                                        <div class="offer-icon"><img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/inner-page/offer.svg') }}" class="img-fluid" alt=""></div>
                                        <div class="offer-name"><h6>Available Offers</h6></div>
                                    </div>
                                    <ul class="offer-detail"><li><p>Pricing, stock validation, and order routing are applied automatically during checkout.</p></li></ul>
                                </div>
                                <button type="submit" class="btn theme-bg-color text-white btn-md w-100 mt-4 fw-bold">Place Order</button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </section>
    <!-- Checkout section End -->

    <!-- Footer Section Start -->
    @include('store.partials.footer')
    <!-- Footer Section End -->

    <!-- Location Modal Start -->
    @include('store.partials.location-modal')
    <!-- Location Modal End -->

    <!-- Add address modal box start -->
    <div class="modal fade theme-modal" id="add-address" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Add a new address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" id="fname" placeholder="Enter First Name">
                            <label for="fname">First Name</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="text" class="form-control" id="lname" placeholder="Enter Last Name">
                            <label for="lname">Last Name</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="email" class="form-control" id="email" placeholder="Enter Email Address">
                            <label for="email">Email Address</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <textarea class="form-control" placeholder="Leave a comment here" id="address"
                                style="height: 100px"></textarea>
                            <label for="address">Enter Address</label>
                        </div>
                    </form>

                    <form>
                        <div class="form-floating mb-4 theme-form-floating">
                            <input type="email" class="form-control" id="pin" placeholder="Enter Pin Code">
                            <label for="pin">Pin Code</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn theme-bg-color btn-md text-white" data-bs-dismiss="modal">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add address modal box end -->

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

    <!-- Lord-icon Js -->
    <script src="{{ asset('fastkart-store/js/lusqsztk.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>

    <!-- feather icon js-->
    <script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Delivery Option js -->
    <script src="{{ asset('fastkart-store/js/delivery-option.js') }}"></script>

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






