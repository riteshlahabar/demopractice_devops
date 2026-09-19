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

    <!-- wow css -->
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/animate.min.css') }}">

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

    @if($storeProduct)
        @include('store.partials.product-detail-main', ['product' => $storeProduct])
    @else
    <!-- Breadcrumb Section Start -->
    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>Creamy Chocolate Cake</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>

                                <li class="breadcrumb-item active">Creamy Chocolate Cake</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Product Left Sidebar Start -->
    <section class="product-section">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-xxl-9 col-xl-8 col-lg-7 wow fadeInUp">
                    <div class="row g-4">
                        <div class="col-xl-6 wow fadeInUp">
                            <div class="product-left-box">
                                <div class="row g-2">
                                    <div class="col-xxl-10 col-lg-12 col-md-10 order-xxl-2 order-lg-1 order-md-2">
                                        <div class="product-main-2 no-arrow">
                                            <div>
                                                <div class="slider-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/1.jpg') }}" id="img-1"
                                                        data-zoom-image="../assets/images/product/category/1.jpg"
                                                        class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="slider-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/2.jpg') }}"
                                                        data-zoom-image="../assets/images/product/category/2.jpg"
                                                        class="img-fluid image_zoom_cls-1 blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="slider-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/3.jpg') }}"
                                                        data-zoom-image="../assets/images/product/category/3.jpg"
                                                        class="img-fluid image_zoom_cls-2 blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="slider-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/4.jpg') }}"
                                                        data-zoom-image="../assets/images/product/category/4.jpg"
                                                        class="img-fluid image_zoom_cls-3 blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="slider-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/5.jpg') }}"
                                                        data-zoom-image="../assets/images/product/category/5.jpg"
                                                        class="img-fluid image_zoom_cls-4 blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="slider-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/6.jpg') }}"
                                                        data-zoom-image="../assets/images/product/category/6.jpg"
                                                        class="img-fluid image_zoom_cls-5 blur-up lazyload" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                                        <div class="left-slider-image-2 left-slider no-arrow slick-top">
                                            <div>
                                                <div class="sidebar-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/1.jpg') }}"
                                                        class="img-fluid blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="sidebar-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/2.jpg') }}"
                                                        class="img-fluid blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="sidebar-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/3.jpg') }}"
                                                        class="img-fluid blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="sidebar-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/4.jpg') }}"
                                                        class="img-fluid blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="sidebar-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/5.jpg') }}"
                                                        class="img-fluid blur-up lazyload" alt="">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="sidebar-image">
                                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/6.jpg') }}"
                                                        class="img-fluid blur-up lazyload" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="right-box-contain">
                                <h6 class="offer-top">30% Off</h6>
                                <h2 class="name">Creamy Chocolate Cake</h2>
                                <div class="price-rating">
                                    <h3 class="theme-color price">$49.50 <del class="text-content">$58.46</del> <span
                                            class="offer theme-color">(8% off)</span></h3>
                                    <div class="product-rating custom-rate">
                                        <ul class="rating">
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star"></i>
                                            </li>
                                        </ul>
                                        <span class="review">23 Customer Review</span>
                                    </div>
                                </div>

                                <div class="product-contain">
                                    <p>Lollipop cake chocolate chocolate cake dessert jujubes. Shortbread sugar plum
                                        dessert
                                        powder cookie sweet brownie. Cake cookie apple pie dessert sugar plum muffin
                                        cheesecake.
                                    </p>
                                </div>

                                <div class="product-package">
                                    <div class="product-title">
                                        <h4>Weight</h4>
                                    </div>
                                    <ul class="select-package">
                                        <li>
                                            <a href="javascript:void(0)" class="active">1/2 KG</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">1 KG</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">1.5 KG</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">Red Roses</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">With Pink Roses</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="time deal-timer product-deal-timer mx-md-0 mx-auto" id="clockdiv-1"
                                    data-hours="1" data-minutes="2" data-seconds="3">
                                    <div class="product-title">
                                        <h4>Hurry up! Sales Ends In</h4>
                                    </div>
                                    <ul>
                                        <li>
                                            <div class="counter d-block">
                                                <div class="days d-block">
                                                    <h5></h5>
                                                </div>
                                                <h6>Days</h6>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="counter d-block">
                                                <div class="hours d-block">
                                                    <h5></h5>
                                                </div>
                                                <h6>Hours</h6>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="counter d-block">
                                                <div class="minutes d-block">
                                                    <h5></h5>
                                                </div>
                                                <h6>Min</h6>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="counter d-block">
                                                <div class="seconds d-block">
                                                    <h5></h5>
                                                </div>
                                                <h6>Sec</h6>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="note-box product-package">
                                    <div class="cart_qty qty-box product-qty">
                                        <div class="input-group">
                                            <button type="button" class="qty-right-plus" data-type="plus" data-field="">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <input class="form-control input-number qty-input" type="text"
                                                name="quantity" value="0">
                                            <button type="button" class="qty-left-minus" data-type="minus"
                                                data-field="">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <button onclick="location.href = 'cart.html';"
                                        class="btn btn-md bg-dark cart-button text-white w-100">Add To Cart</button>
                                </div>

                                <div class="progress-sec">
                                    <div class="left-progressbar">
                                        <h6>Please hurry! Only 5 left in stock</h6>
                                        <div role="progressbar" class="progress warning-progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                style="width: 50%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="buy-box">
                                    <a href="{{ route('store.page', ['page'=>'wishlist']) }}">
                                        <i data-feather="heart"></i>
                                        <span>Add To Wishlist</span>
                                    </a>

                                    <a href="{{ route('store.home') }}">
                                        <i data-feather="shuffle"></i>
                                        <span>Add To Compare</span>
                                    </a>
                                </div>

                                <div class="pickup-box">
                                    <div class="product-title">
                                        <h4>Store Information</h4>
                                    </div>

                                    <div class="pickup-detail">
                                        <h4 class="text-content">Lollipop cake chocolate chocolate cake dessert jujubes.
                                            Shortbread sugar plum dessert powder cookie sweet brownie.</h4>
                                    </div>

                                    <div class="product-info">
                                        <ul class="product-info-list product-info-list-2">
                                            <li>Type : <a href="javascript:void(0)">Black Forest</a></li>
                                            <li>SKU : <a href="javascript:void(0)">SDFVW65467</a></li>
                                            <li>MFG : <a href="javascript:void(0)">Jun 4, 2022</a></li>
                                            <li>Stock : <a href="javascript:void(0)">2 Items Left</a></li>
                                            <li>Tags : <a href="javascript:void(0)">Cake,</a> <a
                                                    href="javascript:void(0)">Backery</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="payment-option">
                                    <div class="product-title">
                                        <h4>Guaranteed Safe Checkout</h4>
                                    </div>
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/payment/1.svg') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/payment/2.svg') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/payment/3.svg') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/payment/4.svg') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/payment/5.svg') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="product-section-box">
                                <ul class="nav nav-tabs custom-nav" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab"
                                            data-bs-target="#description" type="button" role="tab">Description</button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="info-tab" data-bs-toggle="tab"
                                            data-bs-target="#info" type="button" role="tab">Additional info</button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="care-tab" data-bs-toggle="tab"
                                            data-bs-target="#care" type="button" role="tab">Care
                                            Instructions</button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="review-tab" data-bs-toggle="tab"
                                            data-bs-target="#review" type="button" role="tab">Review</button>
                                    </li>
                                </ul>

                                <div class="tab-content custom-tab" id="myTabContent">
                                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                                        <div class="product-description">
                                            <div class="nav-desh">
                                                <p>Jelly beans carrot cake icing biscuit oat cake gummi bears tart.
                                                    Lemon drops carrot cake pudding sweet gummi bears. Chocolate cake
                                                    tart cupcake donut topping liquorice sugar plum chocolate bar. Jelly
                                                    beans tiramisu caramels jujubes biscuit liquorice chocolate. Pudding
                                                    toffee jujubes oat cake sweet roll. Lemon drops dessert croissant
                                                    danish cake cupcake. Sweet roll candy chocolate toffee jelly sweet
                                                    roll halvah brownie topping. Marshmallow powder candy sesame snaps
                                                    jelly beans candy canes marshmallow gingerbread pie.</p>
                                            </div>

                                            <div class="nav-desh">
                                                <div class="desh-title">
                                                    <h5>Organic:</h5>
                                                </div>
                                                <p>vitae et leo duis ut diam quam nulla porttitor massa id neque aliquam
                                                    vestibulum morbi blandit cursus risus at ultrices mi tempus
                                                    imperdiet nulla malesuada pellentesque elit eget gravida cum sociis
                                                    natoque penatibus et magnis dis parturient montes nascetur ridiculus
                                                    mus mauris vitae ultricies leo integer malesuada nunc vel risus
                                                    commodo viverra maecenas accumsan lacus vel facilisis volutpat est
                                                    velit egestas dui id ornare arcu odio ut sem nulla pharetra diam sit
                                                    amet nisl suscipit adipiscing bibendum est ultricies integer quis
                                                    auctor elit sed vulputate mi sit amet mauris commodo quis imperdiet
                                                    massa tincidunt nunc pulvinar sapien et ligula ullamcorper malesuada
                                                    proin libero nunc consequat interdum varius sit amet mattis
                                                    vulputate enim nulla aliquet porttitor lacus luctus accumsan.</p>
                                            </div>

                                            <div class="banner-contain nav-desh">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/vegetable/banner/14.jpg') }}"
                                                    class="bg-img blur-up lazyload" alt="">
                                                <div class="banner-details p-center banner-b-space w-100 text-center">
                                                    <div>
                                                        <h6 class="ls-expanded theme-color mb-sm-3 mb-1">SUMMER</h6>
                                                        <h2>VEGETABLE</h2>
                                                        <p class="mx-auto mt-1">Save up to 5% OFF</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="nav-desh">
                                                <div class="desh-title">
                                                    <h5>From The Manufacturer:</h5>
                                                </div>
                                                <p>Jelly beans shortbread chupa chups carrot cake jelly-o halvah apple
                                                    pie pudding gingerbread. Apple pie halvah cake tiramisu shortbread
                                                    cotton candy croissant chocolate cake. Tart cupcake caramels gummi
                                                    bears macaroon gingerbread fruitcake marzipan wafer. Marzipan
                                                    dessert cupcake ice cream tootsie roll. Brownie chocolate cake
                                                    pudding cake powder candy ice cream ice cream cake. Jujubes soufflÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©
                                                    chupa chups cake candy halvah donut. Tart tart icing lemon drops
                                                    fruitcake apple pie.</p>

                                                <p>Dessert liquorice tart soufflÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â© chocolate bar apple pie pastry danish
                                                    soufflÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©. Gummi bears halvah gingerbread jelly icing. Chocolate cake
                                                    chocolate bar pudding chupa chups bear claw pie dragÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©e donut halvah.
                                                    Gummi bears cookie ice cream jelly-o jujubes sweet croissant.
                                                    Marzipan cotton candy gummi bears lemon drops lollipop lollipop
                                                    chocolate. Ice cream cookie dragÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©e cake sweet roll sweet roll.Lemon
                                                    drops cookie muffin carrot cake chocolate marzipan gingerbread
                                                    topping chocolate bar. SoufflÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â© tiramisu pastry sweet dessert.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="info" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table info-table">
                                                <tbody>
                                                    <tr>
                                                        <td>Specialty</td>
                                                        <td>Vegetarian</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Ingredient Type</td>
                                                        <td>Vegetarian</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Brand</td>
                                                        <td>Lavian Exotique</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Form</td>
                                                        <td>Bar Brownie</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Package Information</td>
                                                        <td>Box</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Manufacturer</td>
                                                        <td>Prayagh Nutri Product Pvt Ltd</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Item part number</td>
                                                        <td>LE 014 - 20pcs CrÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¨me Bakes (Pack of 2)</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Net Quantity</td>
                                                        <td>40.00 count</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="care" role="tabpanel">
                                        <div class="information-box">
                                            <ul>
                                                <li>Store cream cakes in a refrigerator. Fondant cakes should be
                                                    stored in an air conditioned environment.</li>

                                                <li>Slice and serve the cake at room temperature and make sure
                                                    it is not exposed to heat.</li>

                                                <li>Use a serrated knife to cut a fondant cake.</li>

                                                <li>Sculptural elements and figurines may contain wire supports
                                                    or toothpicks or wooden skewers for support.</li>

                                                <li>Please check the placement of these items before serving to
                                                    small children.</li>

                                                <li>The cake should be consumed within 24 hours.</li>

                                                <li>Enjoy your cake!</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="review" role="tabpanel">
                                        <div class="review-box">
                                            <div class="row">
                                                <div class="col-xl-5">
                                                    <div class="product-rating-box">
                                                        <div class="row">
                                                            <div class="col-xl-12">
                                                                <div class="product-main-rating">
                                                                    <h2>3.40
                                                                        <i data-feather="star"></i>
                                                                    </h2>

                                                                    <h5>5 Overall Rating</h5>
                                                                </div>
                                                            </div>

                                                            <div class="col-xl-12">
                                                                <ul class="product-rating-list">
                                                                    <li>
                                                                        <div class="rating-product">
                                                                            <h5>5<i data-feather="star"></i></h5>
                                                                            <div class="progress">
                                                                                <div class="progress-bar"
                                                                                    style="width: 40%;">
                                                                                </div>
                                                                            </div>
                                                                            <h5 class="total">2</h5>
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="rating-product">
                                                                            <h5>4<i data-feather="star"></i></h5>
                                                                            <div class="progress">
                                                                                <div class="progress-bar"
                                                                                    style="width: 20%;">
                                                                                </div>
                                                                            </div>
                                                                            <h5 class="total">1</h5>
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="rating-product">
                                                                            <h5>3<i data-feather="star"></i></h5>
                                                                            <div class="progress">
                                                                                <div class="progress-bar"
                                                                                    style="width: 0%;">
                                                                                </div>
                                                                            </div>
                                                                            <h5 class="total">0</h5>
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="rating-product">
                                                                            <h5>2<i data-feather="star"></i></h5>
                                                                            <div class="progress">
                                                                                <div class="progress-bar"
                                                                                    style="width: 20%;">
                                                                                </div>
                                                                            </div>
                                                                            <h5 class="total">1</h5>
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="rating-product">
                                                                            <h5>1<i data-feather="star"></i></h5>
                                                                            <div class="progress">
                                                                                <div class="progress-bar"
                                                                                    style="width: 20%;">
                                                                                </div>
                                                                            </div>
                                                                            <h5 class="total">1</h5>
                                                                        </div>
                                                                    </li>

                                                                </ul>

                                                                <div class="review-title-2">
                                                                    <h4 class="fw-bold">Review this product</h4>
                                                                    <p>Let other customers know what you think</p>
                                                                    <button class="btn" type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#writereview">Write a
                                                                        review</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-7">
                                                    <div class="review-people">
                                                        <ul class="review-list">
                                                            <li>
                                                                <div class="people-box">
                                                                    <div>
                                                                        <div class="people-image people-text">
                                                                            <img loading="lazy" decoding="async" alt="user" class="img-fluid "
                                                                                src="{{ asset('fastkart-store/images/review/1.jpg') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="people-comment">
                                                                        <div class="people-name"><a
                                                                                href="javascript:void(0)"
                                                                                class="name">Jack Doe</a>
                                                                            <div class="date-time">
                                                                                <h6 class="text-content"> 29 Sep 2023
                                                                                    06:40:PM
                                                                                </h6>
                                                                                <div class="product-rating">
                                                                                    <ul class="rating">
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"></i>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="reply">
                                                                            <p>Avoid this product. The quality is
                                                                                terrible, and
                                                                                it started falling apart almost
                                                                                immediately. I
                                                                                wish I had read more reviews before
                                                                                buying.
                                                                                Lesson learned.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="people-box">
                                                                    <div>
                                                                        <div class="people-image people-text">
                                                                            <img loading="lazy" decoding="async" alt="user" class="img-fluid "
                                                                                src="{{ asset('fastkart-store/images/review/2.jpg') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="people-comment">
                                                                        <div class="people-name"><a
                                                                                href="javascript:void(0)"
                                                                                class="name">Jessica
                                                                                Miller</a>
                                                                            <div class="date-time">
                                                                                <h6 class="text-content"> 29 Sep 2023
                                                                                    06:34:PM
                                                                                </h6>
                                                                                <div class="product-rating">
                                                                                    <div class="product-rating">
                                                                                        <ul class="rating">
                                                                                            <li>
                                                                                                <i data-feather="star"
                                                                                                    class="fill"></i>
                                                                                            </li>
                                                                                            <li>
                                                                                                <i data-feather="star"
                                                                                                    class="fill"></i>
                                                                                            </li>
                                                                                            <li>
                                                                                                <i data-feather="star"
                                                                                                    class="fill"></i>
                                                                                            </li>
                                                                                            <li>
                                                                                                <i data-feather="star"
                                                                                                    class="fill"></i>
                                                                                            </li>
                                                                                            <li>
                                                                                                <i
                                                                                                    data-feather="star"></i>
                                                                                            </li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="reply">
                                                                            <p>Honestly, I regret buying this item. The
                                                                                quality
                                                                                is subpar, and it feels like a waste of
                                                                                money. I
                                                                                wouldn't recommend it to anyone.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="people-box">
                                                                    <div>
                                                                        <div class="people-image people-text">
                                                                            <img loading="lazy" decoding="async" alt="user" class="img-fluid "
                                                                                src="{{ asset('fastkart-store/images/review/3.jpg') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="people-comment">
                                                                        <div class="people-name"><a
                                                                                href="javascript:void(0)"
                                                                                class="name">Rome Doe</a>
                                                                            <div class="date-time">
                                                                                <h6 class="text-content"> 29 Sep 2023
                                                                                    06:18:PM
                                                                                </h6>
                                                                                <div class="product-rating">
                                                                                    <ul class="rating">
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"></i>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="reply">
                                                                            <p>I am extremely satisfied with this
                                                                                purchase. The
                                                                                item arrived promptly, and the quality
                                                                                is
                                                                                exceptional. It's evident that the
                                                                                makers paid
                                                                                attention to detail. Overall, a
                                                                                fantastic buy!
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="people-box">
                                                                    <div>
                                                                        <div class="people-image people-text">
                                                                            <img loading="lazy" decoding="async" alt="user" class="img-fluid "
                                                                                src="{{ asset('fastkart-store/images/review/4.jpg') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="people-comment">
                                                                        <div class="people-name"><a
                                                                                href="javascript:void(0)"
                                                                                class="name">Sarah
                                                                                Davis</a>
                                                                            <div class="date-time">
                                                                                <h6 class="text-content"> 29 Sep 2023
                                                                                    05:58:PM
                                                                                </h6>
                                                                                <div class="product-rating">
                                                                                    <ul class="rating">
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"></i>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="reply">
                                                                            <p>I am genuinely delighted with this item.
                                                                                It's a
                                                                                total winner! The quality is superb, and
                                                                                it has
                                                                                added so much convenience to my daily
                                                                                routine.
                                                                                Highly satisfied customer!</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="people-box">
                                                                    <div>
                                                                        <div class="people-image people-text">
                                                                            <img loading="lazy" decoding="async" alt="user" class="img-fluid "
                                                                                src="{{ asset('fastkart-store/images/review/5.jpg') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="people-comment">
                                                                        <div class="people-name"><a
                                                                                href="javascript:void(0)"
                                                                                class="name">John Doe</a>
                                                                            <div class="date-time">
                                                                                <h6 class="text-content"> 29 Sep 2023
                                                                                    05:22:PM
                                                                                </h6>
                                                                                <div class="product-rating">
                                                                                    <ul class="rating">
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"
                                                                                                class="fill"></i>
                                                                                        </li>
                                                                                        <li>
                                                                                            <i data-feather="star"></i>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="reply">
                                                                            <p>Very impressed with this purchase. The
                                                                                item is of
                                                                                excellent quality, and it has exceeded
                                                                                my
                                                                                expectations.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-xl-4 col-lg-5 d-none d-lg-block wow fadeInUp">
                    <div class="right-sidebar-box">
                        <div class="vendor-box">
                            <div class="vendor-contain">
                                <div class="vendor-image">
                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/vendor.png') }}" class="blur-up lazyload" alt="">
                                </div>

                                <div class="vendor-name">
                                    <h5 class="fw-500">Noodles Co.</h5>

                                    <div class="product-rating mt-1">
                                        <ul class="rating">
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star" class="fill"></i>
                                            </li>
                                            <li>
                                                <i data-feather="star"></i>
                                            </li>
                                        </ul>
                                        <span>(36 Reviews)</span>
                                    </div>

                                </div>
                            </div>

                            <p class="vendor-detail">Noodles & Company is an American fast-casual
                                restaurant that offers international and American noodle dishes and pasta.</p>

                            <div class="vendor-list">
                                <ul>
                                    <li>
                                        <div class="address-contact">
                                            <i data-feather="map-pin"></i>
                                            <h5>Address: <span class="text-content">1288 Franklin Avenue</span></h5>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="address-contact">
                                            <i data-feather="headphones"></i>
                                            <h5>Contact Seller: <span class="text-content">(+1)-123-456-789</span></h5>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Trending Product -->
                        <div class="pt-25">
                            <div class="category-menu">
                                <h3>Trending Products</h3>

                                <ul class="product-list product-right-sidebar border-0 p-0">
                                    <li>
                                        <div class="offer-product">
                                            <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}" class="offer-image">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/vegetable/product/23.png') }}"
                                                    class="img-fluid blur-up lazyload" alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                                        <h6 class="name">Meatigo Premium Goat Curry</h6>
                                                    </a>
                                                    <span>450 G</span>
                                                    <h6 class="price theme-color">$ 70.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="offer-product">
                                            <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}" class="offer-image">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/vegetable/product/24.png') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                                        <h6 class="name">Dates Medjoul Premium Imported</h6>
                                                    </a>
                                                    <span>450 G</span>
                                                    <h6 class="price theme-color">$ 40.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="offer-product">
                                            <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}" class="offer-image">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/vegetable/product/25.png') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                                        <h6 class="name">Good Life Walnut Kernels</h6>
                                                    </a>
                                                    <span>200 G</span>
                                                    <h6 class="price theme-color">$ 52.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="mb-0">
                                        <div class="offer-product">
                                            <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}" class="offer-image">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/vegetable/product/26.png') }}"
                                                    class="blur-up lazyload" alt="">
                                            </a>

                                            <div class="offer-detail">
                                                <div>
                                                    <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                                        <h6 class="name">Apple Red Premium Imported</h6>
                                                    </a>
                                                    <span>1 KG</span>
                                                    <h6 class="price theme-color">$ 80.00</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Banner Section -->
                        <div class="ratio_156 pt-25">
                            @if($storeProduct?->detail_sidebar_banner_image)
                                <a href="{{ $storeProduct->detail_sidebar_banner_url ?: 'javascript:void(0)' }}" class="home-contain d-block">
                                    <img loading="lazy" decoding="async" src="{{ asset($storeProduct->detail_sidebar_banner_image) }}" class="bg-img blur-up lazyload"
                                        alt="{{ $storeProduct->translatedName() }}">
                                </a>
                            @else
                                <div class="home-contain">
                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/vegetable/banner/8.jpg') }}" class="bg-img blur-up lazyload"
                                        alt="">
                                    <div class="home-detail p-top-left home-p-medium">
                                        <div>
                                            <h6 class="text-yellow home-banner">Seafood</h6>
                                            <h3 class="text-uppercase fw-normal"><span
                                                    class="theme-color fw-bold">Freshes</span> Products</h3>
                                            <h3 class="fw-light">every hour</h3>
                                            <button onclick="location.href = 'shop-left-sidebar.html';"
                                                class="btn btn-animation btn-md fw-bold mend-auto">Shop Now <i
                                                    class="fa-solid fa-arrow-right icon"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Left Sidebar End -->

    <!-- Related Product Section Start -->
    <section class="product-list-section section-b-space">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>Related Products</h2>
                <span class="title-leaf">
                    <svg class="icon-width">
                        <use xlink:href="{{ asset('fastkart-store/svg/leaf.svg') }}#leaf"></use>
                    </svg>
                </span>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-6_1 product-wrapper">
                        <div>
                            <div class="product-box-3 wow fadeInUp">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="product-left.htm">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/11.png') }}"
                                                class="img-fluid blur-up lazyload" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Cake</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">Chocolate Chip Cookies 250 g</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                            </ul>
                                            <span>(5.0)</span>
                                        </div>
                                        <h6 class="unit">500 G</h6>
                                        <h5 class="price"><span class="theme-color">$10.25</span> <del>$12.57</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box-3 wow fadeInUp" data-wow-delay="0.05s">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/2.png') }}"
                                                class="img-fluid blur-up lazyload" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Vegetable</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">Fresh Bread and Pastry Flour 200 g</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                            </ul>
                                            <span>(4.0)</span>
                                        </div>
                                        <h6 class="unit">250 ml</h6>
                                        <h5 class="price"><span class="theme-color">$08.02</span> <del>$15.15</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box-3 wow fadeInUp" data-wow-delay="0.1s">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/3.png') }}"
                                                class="img-fluid blur-up lazyload" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Vegetable</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">Peanut Butter Bite Premium Butter Cookies 600 g</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                            </ul>
                                            <span>(2.4)</span>
                                        </div>
                                        <h6 class="unit">350 G</h6>
                                        <h5 class="price"><span class="theme-color">$04.33</span> <del>$10.36</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box-3 wow fadeInUp" data-wow-delay="0.15s">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/4.png') }}"
                                                class="img-fluid blur-up lazyload" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Snacks</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">SnackAmor Combo Pack of Jowar Stick and Jowar Chips</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                            </ul>
                                            <span>(5.0)</span>
                                        </div>
                                        <h6 class="unit">570 G</h6>
                                        <h5 class="price"><span class="theme-color">$12.52</span> <del>$13.62</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box-3 wow fadeInUp" data-wow-delay="0.2s">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/5.png') }}"
                                                class="img-fluid blur-up lazyload" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Snacks</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">Yumitos Chilli Sprinkled Potato Chips 100 g</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                            </ul>
                                            <span>(3.8)</span>
                                        </div>
                                        <h6 class="unit">100 G</h6>
                                        <h5 class="price"><span class="theme-color">$10.25</span> <del>$12.36</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box-3 wow fadeInUp" data-wow-delay="0.25s">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/6.png') }}"
                                                class="img-fluid blur-up lazyload" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Vegetable</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">Fantasy Crunchy Choco Chip Cookies</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                            </ul>
                                            <span>(4.0)</span>
                                        </div>

                                        <h6 class="unit">550 G</h6>

                                        <h5 class="price"><span class="theme-color">$14.25</span> <del>$16.57</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="product-box-3 wow fadeInUp" data-wow-delay="0.3s">
                                <div class="product-header">
                                    <div class="product-image">
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/product/7.png') }}" class="img-fluid" alt="">
                                        </a>

                                        <ul class="product-option">
                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#view">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Compare">
                                                <a href="{{ route('store.home') }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </a>
                                            </li>

                                            <li data-bs-toggle="tooltip" data-bs-placement="top" title="Wishlist">
                                                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                                                    <i data-feather="heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="product-detail">
                                        <span class="span-name">Vegetable</span>
                                        <a href="{{ route('store.page', ['page'=>'product-left-thumbnail']) }}">
                                            <h5 class="name">Fresh Bread and Pastry Flour 200 g</h5>
                                        </a>
                                        <div class="product-rating mt-2">
                                            <ul class="rating">
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star" class="fill"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                                <li>
                                                    <i data-feather="star"></i>
                                                </li>
                                            </ul>
                                            <span>(3.8)</span>
                                        </div>

                                        <h6 class="unit">1 Kg</h6>

                                        <h5 class="price"><span class="theme-color">$12.68</span> <del>$14.69</del>
                                        </h5>
                                        <div class="add-to-cart-box bg-white">
                                            <button class="btn btn-add-cart addcart-button">Add
                                                <span class="add-icon bg-light-gray">
                                                    <i class="fa-solid fa-plus"></i>
                                                </span>
                                            </button>
                                            <div class="cart_qty qty-box">
                                                <div class="input-group bg-white">
                                                    <button type="button" class="qty-left-minus bg-gray"
                                                        data-type="minus" data-field="">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input class="form-control input-number qty-input" type="text"
                                                        name="quantity" value="0">
                                                    <button type="button" class="qty-right-plus bg-gray"
                                                        data-type="plus" data-field="">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Related Product Section End -->
    @endif

    <!-- Footer Section Start -->
    @include('store.partials.footer')
    <!-- Footer Section End -->

    <!-- Quick View Modal Box Start -->
    <div class="modal fade theme-modal view-modal" id="view" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-sm-4 g-2">
                        <div class="col-lg-6">
                            <div class="slider-image">
                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/1.jpg') }}" class="img-fluid blur-up lazyload"
                                    alt="">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="right-sidebar-modal">
                                <h4 class="title-name">Peanut Butter Bite Premium Butter Cookies 600 g</h4>
                                <h4 class="price">$36.99</h4>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                    <span class="ms-2">8 Reviews</span>
                                    <span class="ms-2 text-danger">6 sold in last 16 hours</span>
                                </div>

                                <div class="product-detail">
                                    <h4>Product Details :</h4>
                                    <p>Candy canes sugar plum tart cotton candy chupa chups sugar plum chocolate I love.
                                        Caramels marshmallow icing dessert candy canes I love soufflÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â© I love toffee.
                                        Marshmallow pie sweet sweet roll sesame snaps tiramisu jelly bear claw. Bonbon
                                        muffin I love carrot cake sugar plum dessert bonbon.</p>
                                </div>

                                <ul class="brand-list">
                                    <li>
                                        <div class="brand-box">
                                            <h5>Brand Name:</h5>
                                            <h6>Black Forest</h6>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="brand-box">
                                            <h5>Product Code:</h5>
                                            <h6>W0690034</h6>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="brand-box">
                                            <h5>Product Type:</h5>
                                            <h6>White Cream Cake</h6>
                                        </div>
                                    </li>
                                </ul>

                                <div class="select-size">
                                    <h4>Cake Size :</h4>
                                    <select class="form-select select-form-size">
                                        <option selected>Select Size</option>
                                        <option value="1.2">1/2 KG</option>
                                        <option value="0">1 KG</option>
                                        <option value="1.5">1/5 KG</option>
                                        <option value="red">Red Roses</option>
                                        <option value="pink">With Pink Roses</option>
                                    </select>
                                </div>

                                <div class="modal-button">
                                    <button onclick="location.href = 'cart.html';"
                                        class="btn btn-md add-cart-button icon">Add
                                        To Cart</button>
                                    <button onclick="location.href = 'product-left-thumbnail.html';"
                                        class="btn theme-bg-color view-button icon text-white fw-bold btn-md">
                                        View More Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Quick View Modal Box End -->

    <!-- Location Modal Start -->
    @include('store.partials.location-modal')
    <!-- Location Modal End -->

    <!-- Deal Box Modal Start -->
    @include('store.partials.deal-modal')
    <!-- Deal Box Modal End -->

    <!-- Add to cart Modal Start -->
    <div class="add-cart-box">
        <div class="add-image">
            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cake/pro/1.jpg') }}" class="img-fluid" alt="">
        </div>

        <div class="add-contain">
            <h6>Added to Cart</h6>
        </div>
    </div>
    <!-- Add to cart Modal End -->

    <!-- Tap to top and theme setting button start -->
    <div class="theme-option theme-option-2">
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

    <!-- Sticky Cart Box Start -->
    @if(!$storeProduct)
    <div class="sticky-bottom-cart">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="cart-content">
                        <div class="product-image">
                            <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/product/category/1.jpg') }}" class="img-fluid blur-up lazyload"
                                alt="">
                            <div class="content">
                                <h5>Creamy Chocolate Cake</h5>
                                <h6>$32.96<del class="text-danger">$96.00</del><span>55% off</span></h6>
                            </div>
                        </div>
                        <div class="selection-section">
                            <div class="form-group mb-0">
                                <select id="input-state" class="form-control form-select">
                                    <option selected disabled>Choose Weight...</option>
                                    <option>1/2 KG</option>
                                    <option>1 KG</option>
                                    <option>1.5 KG</option>
                                </select>
                            </div>
                            <div class="cart_qty qty-box product-qty m-0">
                                <div class="input-group h-100">
                                    <button type="button" class="qty-left-minus" data-type="minus" data-field="">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <input class="form-control input-number qty-input" type="text" name="quantity"
                                        value="1">
                                    <button type="button" class="qty-right-plus" data-type="plus" data-field="">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="add-btn">
                            <a class="btn theme-bg-color text-white wishlist-btn" href="{{ route('store.page', ['page'=>'wishlist']) }}"><i
                                    class="fa fa-bookmark"></i> Wishlist</a>
                            <a class="btn theme-bg-color text-white" href="{{ route('store.page', ['page'=>'cart']) }}"><i
                                    class="fas fa-shopping-cart"></i> Add To Cart</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Sticky Cart Box End -->

    <!-- Review Modal Start -->
    <div class="modal fade theme-modal question-modal" id="writereview" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Write a review</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="product-review-form">
                        <div class="product-wrapper">
                            <div class="product-image">
                                <img loading="lazy" decoding="async" class="img-fluid" alt="Solid Collared Tshirts"
                                    src="{{ asset('fastkart-store/images/fashion/product/26.jpg') }}">
                            </div>
                            <div class="product-content">
                                <h5 class="name">Solid Collared Tshirts</h5>
                                <div class="product-review-rating">
                                    <div class="product-rating">
                                        <h6 class="price-number">$16.00</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="review-box">
                            <div class="product-review-rating">
                                <label>Rating</label>
                                <div class="product-rating">
                                    <ul class="rating">
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star" class="fill"></i>
                                        </li>
                                        <li>
                                            <i data-feather="star"></i>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="review-box">
                            <label for="content" class="form-label">Your Question *</label>
                            <textarea id="content" rows="3" class="form-control" placeholder="Your Question"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-md btn-theme-outline fw-bold"
                        data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-md fw-bold text-light theme-bg-color">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Review Modal End -->

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

    <!-- Quantity js -->
    <script src="{{ asset('fastkart-store/js/quantity-2.js') }}"></script>

    <!-- Zoom Js -->
    <script src="{{ asset('fastkart-store/js/jquery.elevatezoom.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/zoom-filter.js') }}"></script>

    <!-- Timer Js -->
    <script src="{{ asset('fastkart-store/js/timer1.js') }}"></script>

    <!-- Sticky-bar js -->
    <script src="{{ asset('fastkart-store/js/sticky-cart-bottom.js') }}"></script>

    <!-- WOW js -->
    <script src="{{ asset('fastkart-store/js/wow.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/custom-wow.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>

</body>

</html>

