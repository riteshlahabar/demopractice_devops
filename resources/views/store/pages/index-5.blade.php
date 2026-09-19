<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Store">
    <meta name="keywords" content="Bawaskar Farmer Store">
    <meta name="author" content="Bawaskar Farmer Store">
    <link rel="icon" href="{{ asset('fastkart-store/images/favicon/5.png') }}" type="image/x-icon">
    <title>Bawaskar Farmer Store</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <!-- bootstrap css -->
    <link id="rtl-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/vendors/bootstrap.css') }}">

    <!-- wow css -->
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/animate.min.css') }}">

    <!-- Plugin CSS file with desired skin css -->
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/vendors/ion.rangeSlider.min.css') }}">

    <!-- animation css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/font-style.css') }}">

    <!-- Template css -->
    <link id="color-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/bawaskar-store.css') }}?v={{ filemtime(public_path('fastkart-store/css/bawaskar-store.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="theme-color-3 dark">
@php
    $cmsBanners = collect(data_get($homeContent ?? [], 'banners', collect()));
    $cmsSections = collect(data_get($homeContent ?? [], 'sections', collect()));
    $homeProductSections = collect(data_get($homeContent ?? [], 'productSections', collect()));
    $cmsBanner = function (string $placement, int $index = 0) use ($cmsBanners) {
        return $cmsBanners->get($placement, collect())->values()->get($index);
    };
    $cmsField = fn ($record, string $field, $fallback = null) => in_array($field, ['title', 'subtitle', 'description', 'button_text'], true) ? storefront_public_t(data_get($record, $field) ?: $fallback, 'homepage_cms') : (data_get($record, $field) ?: $fallback);
    $cmsAsset = fn ($path, string $fallback = '') => $path ? \App\Support\ImageAsset::url($path) : null;
    $cmsSectionTitle = fn (string $key, string $fallback) => storefront_public_t(data_get($cmsSections->get($key), 'title') ?: $fallback, 'homepage_section');
    $heroBanner = $cmsBanner('hero_main');
    $promoBanner0 = $cmsBanner('promo_small', 0);
    $promoBanner1 = $cmsBanner('promo_small', 1);
    $promoBanner2 = $cmsBanner('promo_small', 2);
    $promoBanner3 = $cmsBanner('promo_small', 3);
    $middlePromo0 = $cmsBanner('middle_promo', 0);
    $middlePromo1 = $cmsBanner('middle_promo', 1);
    $footerPromo0 = $cmsBanner('footer_promo', 0);
    $footerPromo1 = $cmsBanner('footer_promo', 1);
    $bankOfferBanners = $cmsBanners->get('bank_offer', collect())->filter(fn ($banner) => filled(data_get($banner, 'image_path')))->values();
    $stripBanners = $cmsBanners->get('strip_banner', collect())->filter(fn ($banner) => filled(data_get($banner, 'image_path')))->values();
    $personalCareBanners = $cmsBanners->get('footer_promo', collect())->filter(fn ($banner) => filled(data_get($banner, 'image_path')))->values();
    $bottomBlogSection = $cmsSections->get('row_16_blog');
    $footerLinks = collect(data_get($homeContent ?? [], 'footerLinks', collect()));
    $fallbackTopSellingRow = $homeProductSections->first(fn ($entry) => data_get($entry, 'section.section_type') === 'top_selling_section');
    $fallbackTopSellingSection = data_get($fallbackTopSellingRow, 'section');
    $fallbackTopSellingAllProducts = collect(data_get($fallbackTopSellingRow, 'products', collect()));
    if ($fallbackTopSellingAllProducts->isEmpty()) {
        $fallbackTopSellingAllProducts = collect($products ?? collect())
            ->filter(fn ($product) => (bool) ($product->is_top_selling ?? false))
            ->values();
    }
    $fallbackDealProduct = $fallbackTopSellingAllProducts->firstWhere('is_deal_timer_product', true) ?: data_get($homeContent ?? [], 'dealTimerProduct');
    $fallbackTopSellingProducts = $fallbackTopSellingAllProducts
        ->filter(fn ($product) => ! $fallbackDealProduct || $product->id !== $fallbackDealProduct->id)
        ->take(8)
        ->values();
    $fallbackTopSellingShowTimer = $fallbackDealProduct
        && $fallbackDealProduct->is_offer_active
        && $fallbackDealProduct->offer_end_at
        && $fallbackDealProduct->offer_end_at->isFuture();
@endphp

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
    @include('store.partials.header-home')
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

    @if(collect(data_get($homeContent ?? [], 'homepageRows', collect()))->isNotEmpty())
        @include('store.partials.homepage-setting-sections')
    @else
    @if($heroBanner && filled(data_get($heroBanner, 'image_path')))
    <!-- Home Section Start -->
    <section class="home-section-2 home-section-bg pt-0 overflow-hidden">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12">
                    <div class="slider-animate">
                        <div>
                            <div class="home-contain rounded-0 p-0">
                                <img loading="eager" fetchpriority="high" decoding="async" src="{{ $cmsAsset($cmsField($heroBanner, 'image_path')) }}"
                                    class="img-fluid bg-img blur-up lazyload" alt="">
                                <div class="home-detail home-big-space p-center-left home-overlay position-relative">
                                    <div class="container-fluid-lg">
                                        <div>
                                            @if(!empty($cmsField($heroBanner, 'subtitle')))
                                                <h6 class="ls-expanded theme-color text-uppercase">{{ $cmsField($heroBanner, 'subtitle') }}</h6>
                                            @endif

                                            @if(!empty($cmsField($heroBanner, 'title')))
                                                <h1 class="heding-2">{{ $cmsField($heroBanner, 'title') }}</h1>
                                            @endif

                                            @if(!empty($cmsField($heroBanner, 'description')))
                                                <h5 class="text-content">{{ $cmsField($heroBanner, 'description') }}</h5>
                                            @endif

                                            <button
                                                class="btn theme-bg-color btn-md text-white fw-bold mt-md-4 mt-2 mend-auto"
                                                onclick="location.href = '{{ data_get($heroBanner, 'button_url') ?: route('store.page', ['page'=>'shop-left-sidebar']) }}';">{{ $cmsField($heroBanner, 'button_text', 'Shop Now') }} <i class="fa-solid fa-arrow-right icon"></i></button>
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
    <!-- Home Section End -->
    @endif

    <!-- Banner Section Start -->
    @php($fallbackPromoBanners = collect([$promoBanner0, $promoBanner1, $promoBanner2, $promoBanner3])->filter(fn ($banner) => $banner && filled(data_get($banner, 'image_path')))->values())
    @if($fallbackPromoBanners->isNotEmpty())
        <section class="banner-section banner-small ratio_65">
            <div class="container-fluid-lg">
                <div class="slider-4-banner no-arrow slick-height">
                    @foreach($fallbackPromoBanners as $promoBanner)
                        @php($promoUrl = data_get($promoBanner, 'button_url') ?: route('store.page', ['page'=>'shop-left-sidebar']))
                        <div>
                            <div class="banner-contain-3 hover-effect">
                                <a href="{{ $promoUrl }}">
                                    <img loading="lazy" decoding="async" src="{{ $cmsAsset($cmsField($promoBanner, 'image_path')) }}" class="bg-img blur-up lazyload" alt="{{ $cmsField($promoBanner, 'title') }}">
                                </a>
                                <div class="banner-detail p-center-left w-75 banner-p-sm mend-auto">
                                    <div>
                                        @if(!empty($cmsField($promoBanner, 'subtitle')))<h5 class="fw-light mb-2">{{ $cmsField($promoBanner, 'subtitle') }}</h5>@endif
                                        @if(!empty($cmsField($promoBanner, 'title')))<h4 class="fw-bold mb-0">{{ $cmsField($promoBanner, 'title') }}</h4>@endif
                                        <button onclick="location.href = '{{ $promoUrl }}';" class="btn shop-now-button mt-3 ps-0 mend-auto theme-color fw-bold">{{ $cmsField($promoBanner, 'button_text', 'Shop Now') }} <i class="fa-solid fa-chevron-right"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- Banner Section End -->

    <!-- Category Section Start -->
    <section class="category-section-3">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>{{ $cmsSectionTitle('shop_by_categories', 'Shop By Categories') }}</h2>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="category-slider-1 arrow-slider wow fadeInUp">
                        @include('store.partials.category-slider')
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Category Section End -->

    @if($homeProductSections->isNotEmpty())
        @include('store.partials.home-product-sections')
    @endif


    <!-- Service Section Start -->
    <section class="service-section section-b-space">
        <div class="container-fluid-lg">
            <div class="row g-3 row-cols-xxl-5 row-cols-lg-3 row-cols-md-2">
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon-4.svg') }}#shipping"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Free Shipping</h3>
                            <h6 class="text-content">Free Shipping world wide</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon-4.svg') }}#service"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>{{ storefront_public_t('24 x 7 Service', 'service') }}</h3>
                            <h6 class="text-content">{{ storefront_public_t('Online Service For 24 x 7', 'service') }}</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon-4.svg') }}#pay"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Online Pay</h3>
                            <h6 class="text-content">Online Payment Avaible</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon-4.svg') }}#offer"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>Festival Offer</h3>
                            <h6 class="text-content">Super Sale Upto 50% off</h6>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="service-contain-2">
                        <svg class="icon-width">
                            <use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon-4.svg') }}#return"></use>
                        </svg>
                        <div class="service-detail">
                            <h3>100% Original</h3>
                            <h6 class="text-content">100% Money Back</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Service Section End -->

    @endif

    <!-- Footer Start -->
    @include('store.partials.footer-home')
    <!-- Footer End -->

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
                                        Caramels marshmallow icing dessert candy canes I love souffle I love toffee.
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

    <!-- Cookie Bar Box Start -->
    <div class="cookie-bar-box">
        <div class="cookie-box">
            <div class="cookie-image">
                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/cookie-bar.png') }}" class="blur-up lazyload" alt="">
                <h2>Cookies!</h2>
            </div>

            <div class="cookie-contain">
                <h5 class="text-content">We use cookies to make your experience better</h5>
            </div>
        </div>

        <div class="button-group">
            <button class="btn privacy-button">Privacy Policy</button>
            <button class="btn ok-button">OK</button>
        </div>
    </div>
    <!-- Cookie Bar Box End -->

    <!-- Location Modal Start -->
    @include('store.partials.location-modal')
    <!-- Location Modal End -->

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
                                        value="#239698" title="Choose your color">
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

    <!-- Range slider js -->
    <script src="{{ asset('fastkart-store/js/ion.rangeSlider.min.js') }}"></script>

    <!-- Auto Height Js -->
    <script src="{{ asset('fastkart-store/js/auto-height.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Quantity js -->
    <script src="{{ asset('fastkart-store/js/quantity-2.js') }}"></script>

    <!-- Fly Cart Js -->
    <script src="{{ asset('fastkart-store/js/fly-cart.js') }}"></script>

    <!-- Timer Js -->
    <script src="{{ asset('fastkart-store/js/timer1.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/timer2.js') }}"></script>

    <!-- Copy clipboard Js -->
    <script src="{{ asset('fastkart-store/js/clipboard.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/copy-clipboard.js') }}"></script>

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


