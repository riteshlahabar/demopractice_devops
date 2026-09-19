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
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">

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
    @php
        $aboutPage = $aboutPage ?? null;
        $aboutHighlights = collect($aboutHighlights ?? []);
        $aboutStats = collect($aboutStats ?? []);
        $aboutTeam = collect($aboutTeam ?? []);
        $aboutMedia = static function (?string $path, ?string $fallback = null): ?string {
            $path = trim((string) $path);

            if ($path === '') {
                return $fallback ? asset($fallback) : null;
            }

            return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
        };
        $aboutIntroParagraphs = $aboutPage ? $aboutPage->introParagraphs() : [];
    @endphp

    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>{{ web_t('nav.about_us', 'About Us') }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">{{ web_t('nav.about_us', 'About Us') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Fresh Vegetable Section Start -->
    @if ($aboutPage)
    <section class="fresh-vegetable-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="row gx-xl-5 gy-xl-0 g-3 ratio_148_1">
                <div class="col-xl-6 col-12">
                    <div class="row g-sm-4 g-2">
                        <div class="col-6">
                            <div class="fresh-image-2">
                                <div>
                                    <img loading="lazy" decoding="async" src="{{ $aboutMedia($aboutPage->image_one_path, 'fastkart-store/images/inner-page/about-us/1.jpg') }}"
                                        class="bg-img blur-up lazyload" alt="{{ $aboutPage->intro_heading }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="fresh-image">
                                <div>
                                    <img loading="lazy" decoding="async" src="{{ $aboutMedia($aboutPage->image_two_path, 'fastkart-store/images/inner-page/about-us/2.jpg') }}"
                                        class="bg-img blur-up lazyload" alt="{{ $aboutPage->intro_heading }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-12">
                    <div class="fresh-contain p-center-left">
                        <div>
                            <div class="review-title">
                                @if (filled($aboutPage->intro_label))
                                    <h4>{{ storefront_public_t($aboutPage->intro_label, 'about') }}</h4>
                                @endif
                                @if (filled($aboutPage->intro_heading))
                                    <h2>{{ storefront_public_t($aboutPage->intro_heading, 'about') }}</h2>
                                @endif
                            </div>

                            <div class="delivery-list">
                                @foreach ($aboutIntroParagraphs as $aboutParagraph)
                                    <p class="text-content">{{ storefront_public_t($aboutParagraph, 'about') }}</p>
                                @endforeach

                                @if ($aboutHighlights->isNotEmpty())
                                    <ul class="delivery-box">
                                        @foreach ($aboutHighlights as $aboutHighlight)
                                            <li>
                                                <div class="delivery-box">
                                                    @if ($aboutMedia($aboutHighlight->icon_path))
                                                        <div class="delivery-icon">
                                                            <img loading="lazy" decoding="async" src="{{ $aboutMedia($aboutHighlight->icon_path) }}" class="blur-up lazyload" alt="{{ $aboutHighlight->title }}">
                                                        </div>
                                                    @endif

                                                    <div class="delivery-detail">
                                                        <h5 class="text">{{ storefront_public_t($aboutHighlight->title, 'about') }}</h5>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    <!-- Fresh Vegetable Section End -->

    <!-- Client Section Start -->
    @if ($aboutStats->isNotEmpty())
    <section class="client-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="about-us-title text-center">
                        @if (filled($aboutPage?->stats_label))
                            <h4>{{ storefront_public_t($aboutPage->stats_label, 'about') }}</h4>
                        @endif
                        @if (filled($aboutPage?->stats_heading))
                            <h2 class="center">{{ storefront_public_t($aboutPage->stats_heading, 'about') }}</h2>
                        @endif
                    </div>

                    <div class="slider-3_1 product-wrapper">
                        @foreach ($aboutStats as $aboutStat)
                            <div>
                                <div class="clint-contain">
                                    @if ($aboutMedia($aboutStat->icon_path))
                                        <div class="client-icon">
                                            <img loading="lazy" decoding="async" src="{{ $aboutMedia($aboutStat->icon_path) }}" class="blur-up lazyload" alt="{{ $aboutStat->title }}">
                                        </div>
                                    @endif
                                    @if (filled($aboutStat->value))
                                        <h2>{{ $aboutStat->value }}</h2>
                                    @endif
                                    <h4>{{ storefront_public_t($aboutStat->title, 'about') }}</h4>
                                    @if (filled($aboutStat->description))
                                        <p>{{ storefront_public_t($aboutStat->description, 'about') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    <!-- Client Section End -->

    <!-- Team Section Start -->
    @if ($aboutTeam->isNotEmpty())
    <section class="team-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="about-us-title text-center">
                @if (filled($aboutPage?->team_label))
                    <h4 class="text-content">{{ storefront_public_t($aboutPage->team_label, 'about') }}</h4>
                @endif
                @if (filled($aboutPage?->team_heading))
                    <h2 class="center">{{ storefront_public_t($aboutPage->team_heading, 'about') }}</h2>
                @endif
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-user product-wrapper">
                        @foreach ($aboutTeam as $aboutMember)
                            <div>
                                <div class="team-box">
                                    @if ($aboutMedia($aboutMember->photo_path))
                                        <div class="team-image">
                                            <img loading="lazy" decoding="async" src="{{ $aboutMedia($aboutMember->photo_path) }}" class="img-fluid blur-up lazyload" alt="{{ $aboutMember->name }}">
                                        </div>
                                    @endif

                                    <div class="team-name">
                                        <h3>{{ $aboutMember->name }}</h3>
                                        @if (filled($aboutMember->role))
                                            <h5>{{ storefront_public_t($aboutMember->role, 'about') }}</h5>
                                        @endif
                                        @if (filled($aboutMember->bio))
                                            <p>{{ storefront_public_t($aboutMember->bio, 'about') }}</p>
                                        @endif
                                        @php($aboutSocials = $aboutMember->socialLinks())
                                        @if ($aboutSocials !== [])
                                            <ul class="team-media">
                                                @foreach ($aboutSocials as $aboutSocial)
                                                    <li>
                                                        <a href="{{ $aboutSocial['url'] }}" class="{{ $aboutSocial['class'] }}" target="_blank" rel="noopener">
                                                            <i class="{{ $aboutSocial['icon'] }}"></i>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    <!-- Team Section End -->

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
    <script src="{{ asset('fastkart-store/js/slick/slick-animation.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>

