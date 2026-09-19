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
                        <h2>Order Tracking</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Order Tracking</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <?php
        // These aliases keep the tracking page compatible with all previous variable names.
        $trackedOrder = $trackedOrder ?? $activeTrackedOrder ?? $storeTrackedOrder ?? $storeLastOrder ?? $storeOrders->first();
        $activeTrackedOrder = $activeTrackedOrder ?? $trackedOrder;
        $storeTrackedOrder = $storeTrackedOrder ?? $trackedOrder;
    ?>

    <!-- Order Detail Section Start -->
    <section class="order-detail">
        <div class="container-fluid-lg">
            @if(! $storeUser)
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning store-tracking-empty mb-0">
                            Please <a href="{{ route('store.page', ['page' => 'login']) }}">login</a> to track your live order status.
                        </div>
                    </div>
                </div>
            @elseif($storeOrders->isEmpty() || ! ($trackedOrder ?? $storeTrackedOrder ?? $storeLastOrder ?? $storeOrders->first()))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-light store-tracking-empty mb-0">
                            No order found for tracking yet. <a href="{{ route('store.page', ['page' => 'shop-left-sidebar']) }}">Continue shopping</a>.
                        </div>
                    </div>
                </div>
            @else
                <?php
                    $firstItem = $trackedOrder->items->first();
                    $trackedProduct = $firstItem?->product;
                    $trackedImage = optional($trackedProduct?->images?->first())->url ?: asset('fastkart-store/images/vegetable/product/1.png');
                    $latestDispatch = $trackedOrder->dispatches->sortByDesc(fn ($dispatch) => $dispatch->delivered_at?->timestamp ?? $dispatch->dispatched_at?->timestamp ?? $dispatch->created_at?->timestamp ?? 0)->first();
                    $trackingCode = $latestDispatch?->tracking_no ?: ($latestDispatch?->dispatch_no ?: $trackedOrder->order_no);
                    $courierName = $latestDispatch?->courier_name ?: 'Bawaskar Delivery Desk';
                    $packageInfo = $trackedOrder->items->count().' item(s) | Rs. '.number_format((float) $trackedOrder->grand_total, 2);
                    $originLabel = 'Dr. Bawaskar Technology Warehouse';
                    $destinationLabel = collect([
                        $trackedOrder->contact_name,
                        $trackedOrder->address_line1,
                        $trackedOrder->address_line2,
                        trim(collect([$trackedOrder->city, $trackedOrder->state])->filter()->implode(', ')),
                        $trackedOrder->pincode,
                    ])->filter(fn ($value) => filled($value))->implode(', ');
                    $statusLabel = ucwords(str_replace('_', ' ', (string) ($trackedOrder->status ?: 'pending')));
                    $paymentLabel = ucwords(str_replace('_', ' ', (string) ($trackedOrder->payment_status ?: 'pending')));
                    $statusStepMap = [
                        'salesman_review' => 1,
                        'admin_review' => 2,
                        'approved' => 2,
                        'packing' => 3,
                        'dispatched' => 4,
                        'delivered' => 5,
                    ];
                    $currentStep = $trackedOrder->status === 'cancelled' ? 1 : ($statusStepMap[$trackedOrder->status] ?? 1);
                    $progressSteps = [
                        ['label' => 'Order Placed', 'time' => $trackedOrder->created_at?->format('d M Y, h:i A') ?: 'Pending'],
                        ['label' => 'Reviewed', 'time' => in_array((string) $trackedOrder->status, ['admin_review', 'approved', 'packing', 'dispatched', 'delivered'], true) ? (($trackedOrder->approved_at ?: $trackedOrder->updated_at ?: $trackedOrder->created_at)?->format('d M Y, h:i A') ?: 'Completed') : 'Pending'],
                        ['label' => 'Packed', 'time' => in_array((string) $trackedOrder->status, ['packing', 'dispatched', 'delivered'], true) ? (($trackedOrder->updated_at ?: $trackedOrder->approved_at ?: $trackedOrder->created_at)?->format('d M Y, h:i A') ?: 'Completed') : 'Pending'],
                        ['label' => 'Dispatched', 'time' => $latestDispatch?->dispatched_at?->format('d M Y, h:i A') ?: 'Pending'],
                        ['label' => 'Delivered', 'time' => $latestDispatch?->delivered_at?->format('d M Y, h:i A') ?: 'Pending'],
                    ];
                    $trackingHistory = [
                        ['label' => 'Order Placed', 'moment' => $trackedOrder->created_at, 'location' => 'Storefront Checkout'],
                    ];
                    if (in_array((string) $trackedOrder->status, ['admin_review', 'approved', 'packing', 'dispatched', 'delivered'], true)) {
                        $trackingHistory[] = ['label' => 'Sales/Admin Review Completed', 'moment' => $trackedOrder->approved_at ?: $trackedOrder->updated_at ?: $trackedOrder->created_at, 'location' => 'Order Review Desk'];
                    }
                    if (in_array((string) $trackedOrder->status, ['packing', 'dispatched', 'delivered'], true)) {
                        $trackingHistory[] = ['label' => 'Packed For Dispatch', 'moment' => $trackedOrder->updated_at ?: $trackedOrder->approved_at ?: $trackedOrder->created_at, 'location' => $originLabel];
                    }
                    if ($latestDispatch?->dispatched_at) {
                        $trackingHistory[] = ['label' => 'Dispatched', 'moment' => $latestDispatch->dispatched_at, 'location' => $courierName];
                    }
                    if ($latestDispatch?->delivered_at) {
                        $trackingHistory[] = ['label' => 'Delivered', 'moment' => $latestDispatch->delivered_at, 'location' => $destinationLabel ?: 'Delivery Address'];
                    }
                    if ($trackedOrder->status === 'cancelled') {
                        $trackingHistory[] = ['label' => 'Order Cancelled', 'moment' => $trackedOrder->updated_at ?: $trackedOrder->created_at, 'location' => 'Order Management Desk'];
                    }
                ?>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="summery-box store-tracking-selector">
                            <div class="summery-header">
                                <h3>Track Live Order</h3>
                                <h5 class="ms-auto theme-color">{{ $storeOrders->count() }} Recent Orders</h5>
                            </div>
                            <form action="{{ route('store.page', ['page' => 'order-tracking']) }}" method="GET" class="row g-3 align-items-end">
                                <div class="col-lg-8 col-md-7">
                                    <label for="tracked-order" class="form-label">Select Order</label>
                                    <select id="tracked-order" name="order" class="form-select">
                                        @foreach($storeOrders as $orderOption)
                                            <option value="{{ $orderOption->order_no }}" @selected($orderOption->id === $trackedOrder->id)>
                                                {{ $orderOption->order_no }} - {{ $orderOption->created_at?->format('d M Y') ?: 'N/A' }} - Rs. {{ number_format((float) $orderOption->grand_total, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-5">
                                    <button type="submit" class="btn theme-bg-color text-white w-100">View Tracking</button>
                                </div>
                            </form>
                            <p class="store-tracking-note mb-0">Current status: <strong>{{ $statusLabel }}</strong> | Payment: <strong>{{ $paymentLabel }}</strong></p>
                        </div>
                    </div>
                </div>

                <div class="row g-sm-4 g-3">
                    <div class="col-xxl-3 col-xl-4 col-lg-6">
                        <div class="order-image">
                            <img loading="lazy" decoding="async" src="{{ $trackedImage }}" class="img-fluid blur-up lazyload" alt="{{ $trackedProduct?->translatedName() ?: 'Tracked order item' }}">
                        </div>
                    </div>

                    <div class="col-xxl-9 col-xl-8 col-lg-6">
                        <div class="row g-sm-4 g-3">
                            <div class="col-xl-4 col-sm-6"><div class="order-details-contain"><div class="order-tracking-icon"><i data-feather="package" class="text-content"></i></div><div class="order-details-name"><h5 class="text-content">Tracking Code</h5><h2 class="theme-color">{{ $trackingCode }}</h2></div></div></div>
                            <div class="col-xl-4 col-sm-6"><div class="order-details-contain"><div class="order-tracking-icon"><i data-feather="truck" class="text-content"></i></div><div class="order-details-name"><h5 class="text-content">Service</h5><h4>{{ $courierName }}</h4>@if($latestDispatch?->tracking_url)<a href="{{ $latestDispatch->tracking_url }}" target="_blank" rel="noopener" class="store-order-inline-link">Open courier tracking</a>@endif</div></div></div>
                            <div class="col-xl-4 col-sm-6"><div class="order-details-contain"><div class="order-tracking-icon"><i class="text-content" data-feather="info"></i></div><div class="order-details-name"><h5 class="text-content">Package Info</h5><h4>{{ $packageInfo }}</h4></div></div></div>
                            <div class="col-xl-4 col-sm-6"><div class="order-details-contain"><div class="order-tracking-icon"><i class="text-content" data-feather="crosshair"></i></div><div class="order-details-name"><h5 class="text-content">From</h5><h4>{{ $originLabel }}</h4></div></div></div>
                            <div class="col-xl-4 col-sm-6"><div class="order-details-contain"><div class="order-tracking-icon"><i class="text-content" data-feather="map-pin"></i></div><div class="order-details-name"><h5 class="text-content">Destination</h5><h4>{{ $destinationLabel ?: 'Address not available' }}</h4></div></div></div>
                            <div class="col-xl-4 col-sm-6"><div class="order-details-contain"><div class="order-tracking-icon"><i class="text-content" data-feather="calendar"></i></div><div class="order-details-name"><h5 class="text-content">Last Update</h5><h4>{{ ($latestDispatch?->delivered_at ?: $latestDispatch?->dispatched_at ?: $trackedOrder->approved_at ?: $trackedOrder->updated_at ?: $trackedOrder->created_at)?->format('d M Y, h:i A') ?: 'Pending' }}</h4></div></div></div>

                            <div class="col-12 overflow-hidden">
                                <ol class="progtrckr">
                                    @foreach($progressSteps as $index => $step)
                                        <?php $stepNumber = $index + 1; $stepClass = $stepNumber <= $currentStep ? 'progtrckr-done' : 'progtrckr-todo'; ?>
                                        <li class="{{ $stepClass }}{{ $stepNumber === $currentStep ? ' store-progress-current' : '' }}">
                                            <h5>{{ $step['label'] }}</h5>
                                            <h6>{{ $step['time'] }}</h6>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>

                            @if($trackedOrder->status === 'cancelled')
                                <div class="col-12">
                                    <div class="alert alert-danger mb-0">This order was cancelled. Please contact support if you need help with a replacement or refund.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- Order Detail Section End -->

    <!-- Order Table Section Start -->
    <section class="order-table-section section-b-space">
        <div class="container-fluid-lg">
            @if($storeUser && ($trackedOrder ?? $storeTrackedOrder ?? $storeLastOrder ?? $storeOrders->first()))
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table order-tab-table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>{{ web_t('header.location', 'Location') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trackingHistory as $history)
                                        <tr>
                                            <td>{{ $history['label'] }}</td>
                                            <td>{{ $history['moment']?->format('d M Y') ?: 'Pending' }}</td>
                                            <td>{{ $history['moment']?->format('h:i A') ?: 'Pending' }}</td>
                                            <td>{{ $history['location'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- Order Table Section End -->

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
    <script src="{{ asset('fastkart-store/js/slick/slick-animation.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/custom-slick-animated.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- Price Range Js -->
    <script src="{{ asset('fastkart-store/js/ion.rangeSlider.min.js') }}"></script>

    <!-- sidebar open js -->
    <script src="{{ asset('fastkart-store/js/filter-sidebar.js') }}"></script>

    <!-- Zoom Js -->
    <script src="{{ asset('fastkart-store/js/jquery.elevatezoom.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/zoom-filter.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>

</body>

</html>







