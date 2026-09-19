@php
    $company = $companySetting ?? null;
    $companyAddress = filled($company?->address) ? trim((string) $company->address) : null;
    $companyPhone = filled($company?->phone) ? trim((string) $company->phone) : null;
    $companyEmail = filled($company?->email) ? trim((string) $company->email) : null;
    $menuCategories = collect(data_get($storefrontNavigation ?? [], 'categoryMenu', collect()));
    $shopUrl = route('store.page', ['page' => 'shop-left-sidebar']);
    $topbarMessages = collect(data_get($storefrontNavigation ?? [], 'topbarMessages', collect()));
    $hasDealProducts = collect(data_get($storefrontNavigation ?? [], 'dealProducts', collect()))->isNotEmpty();
@endphp
    <header class="pb-md-4 pb-0">
        <div class="header-top">
            <div class="container-fluid-lg">
                <div class="row">
                    <div class="col-xxl-3 d-xxl-block d-none">
                        <div class="top-left-header">
                            @if($companyAddress)
                                <i class="iconly-Location icli text-white"></i>
                                <span class="text-white">{{ $companyAddress }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-xxl-6 col-lg-9 d-lg-block d-none">
                        @if ($topbarMessages->isNotEmpty())
                        <div class="header-offer">
                            <div class="notification-slider">
                                @foreach ($topbarMessages as $topbarMessage)
                                <div>
                                    <div class="timer-notification">
                                        <h6>@if (filled($topbarMessage->heading))<strong class="me-1">{{ storefront_public_t($topbarMessage->heading, 'topbar') }}</strong>@endif{{ storefront_public_t($topbarMessage->message, 'topbar') }}
                                            @if (filled($topbarMessage->link_label) && $topbarMessage->linkHref())
                                            <a href="{{ $topbarMessage->linkHref() }}" class="text-white">{{ storefront_public_t($topbarMessage->link_label, 'topbar') }}</a>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="col-lg-3">
                        <ul class="about-list right-nav-about">
                            @include('store.partials.topbar-language-currency')
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="top-nav top-header sticky-header">
            <div class="container-fluid-lg">
                <div class="row">
                    <div class="col-12">
                        <div class="navbar-top">
                            <button class="navbar-toggler d-xl-none d-inline navbar-menu-button" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#primaryMenu">
                                <span class="navbar-toggler-icon">
                                    <i class="fa-solid fa-bars"></i>
                                </span>
                            </button>
                            <a href="{{ route('store.home') }}" class="web-logo nav-logo">
                                <span class="bawaskar-store-logo">
                                    <img decoding="async" src="{{ asset('logo/logo.png') }}" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
                                    <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                                </span>
                            </a>

                            <div class="middle-box">
                                <div class="location-box">
                                    <button class="btn location-button" data-bs-toggle="modal"
                                        data-bs-target="#locationModal">
                                        <span class="location-arrow">
                                            <i data-feather="map-pin"></i>
                                        </span>
                                        <span class="locat-name">{{ $selectedDeliveryArea['name'] ?? web_t('header.your_location', 'Your Location') }}</span>
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </div>

                                <div class="search-box">
                                    <form action="{{ $shopUrl }}" method="GET" role="search">
                                        <div class="input-group">
                                            <input type="search" name="search" value="{{ $searchQuery ?? '' }}" class="form-control" placeholder="{{ web_t('header.search_placeholder', 'Search for products') }}">
                                            <button class="btn" type="submit" id="button-addon2">
                                                <i data-feather="search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="rightside-box">
                                <div class="search-full">
                                    <form action="{{ $shopUrl }}" method="GET" role="search">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i data-feather="search" class="font-light"></i>
                                            </span>
                                            <input type="text" name="search" value="{{ $searchQuery ?? '' }}" class="form-control search-type" placeholder="{{ web_t('header.search_placeholder', 'Search for products') }}">
                                            <span class="input-group-text close-search">
                                                <i data-feather="x" class="font-light"></i>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                @include('store.partials.header-actions')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="header-nav">
                        <div class="header-nav-left">
                            <button class="dropdown-category">
                                <i data-feather="align-left"></i>
                                <span>All Categories</span>
                            </button>

                            <div class="category-dropdown">
                                <div class="category-title">
                                    <h5>{{ web_t('nav.categories', 'Categories') }}</h5>
                                    <button type="button" class="btn p-0 close-button text-content">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <ul class="category-list">
                                    @forelse($menuCategories as $menuCategory)
                                        @php
                                            $menuCategoryUrl = $menuCategory->slug
                                                ? route('store.category', ['category' => $menuCategory->slug])
                                                : $shopUrl;
                                            $menuCategoryProducts = collect($menuCategory->menu_products ?? []);
                                        @endphp
                                        <li class="onhover-category-list">
                                            <a href="{{ $menuCategoryUrl }}" class="category-name">
                                                <img loading="lazy" decoding="async" src="{{ $menuCategory->storefront_image_url ?: asset('fastkart-store/svg/1/grocery.svg') }}" alt="{{ $menuCategory->storefront_name }}">
                                                <h6>{{ $menuCategory->storefront_name }}</h6>
                                                @if($menuCategoryProducts->isNotEmpty())
                                                    <i class="fa-solid fa-angle-right"></i>
                                                @endif
                                            </a>

                                            @if($menuCategoryProducts->isNotEmpty())
                                                <div class="onhover-category-box">
                                                    <div class="list-1">
                                                        <div class="category-title-box">
                                                            <h5>{{ $menuCategory->storefront_name }}</h5>
                                                        </div>
                                                        <ul>
                                                            @foreach($menuCategoryProducts as $menuCategoryProduct)
                                                                <li>
                                                                    <a href="{{ route('store.product', ['product' => $menuCategoryProduct->id]) }}">{{ $menuCategoryProduct->translatedName() }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </li>
                                    @empty
                                        <li class="onhover-category-list">
                                            <a href="{{ $shopUrl }}" class="category-name">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/svg/1/grocery.svg') }}" alt="">
                                                <h6>{{ web_t('nav.all_categories', 'All Categories') }}</h6>
                                            </a>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="header-nav-middle">
                            <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky">
                                <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                    <div class="offcanvas-header navbar-shadow">
                                        <h5>Menu</h5>
                                        <button class="btn-close lead" type="button"
                                            data-bs-dismiss="offcanvas"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        @include('store.partials.navbar')
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="header-nav-right">
                            @if ($hasDealProducts)
                            <button class="btn deal-button" data-bs-toggle="modal" data-bs-target="#deal-box">
                                <i data-feather="zap"></i>
                                <span>{{ web_t('deal.title', 'Deal Today') }}</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
