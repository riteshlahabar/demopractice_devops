    @php
        $homeShopUrl = route('store.page', ['page' => 'shop-left-sidebar']);
        $homeCompanyPhone = filled(($companySetting ?? null)?->phone) ? trim((string) $companySetting->phone) : null;
        $homeWishlistCount = (int) ($storeWishlistCount ?? 0);
        $homeCartCount = rtrim(rtrim(number_format((float) ($storeCartCount ?? 0), 3, '.', ''), '0'), '.');
        $homeCartCount = $homeCartCount !== '' ? $homeCartCount : '0';
        $homeUserRole = $storeUser?->role === 'dealer' ? 'Dealer' : 'Customer';
    @endphp
    <header class="header-3">
        <div class="top-nav sticky-header sticky-header-2">
            <div class="container-fluid-lg">
                <div class="row">
                    <div class="col-12">
                        <div class="navbar-top">
                            <button class="navbar-toggler d-xl-none d-block p-0 me-3" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#primaryMenu">
                                <span class="navbar-toggler-icon">
                                    <i class="iconly-Category icli"></i>
                                </span>
                            </button>
                            <a href="{{ route('store.home') }}" class="web-logo nav-logo">
                                <span class="bawaskar-store-logo">
                                    <img decoding="async" src="{{ asset('logo/logo.png') }}" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
                                    <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                                </span>
                            </a>

                            <div class="search-full">
                                <form action="{{ $homeShopUrl }}" method="GET" role="search">
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

                            <div class="middle-box">
                                <div class="center-box">
                                    <div class="location-box-2">
                                        <button class="btn location-button" data-bs-toggle="modal"
                                            data-bs-target="#locationModal">
                                            <i class="iconly-Location icli"></i>
                                            <span>{{ $selectedDeliveryArea['name'] ?? web_t('header.location', 'Location') }}</span>
                                            <i class="fa-solid fa-angle-down down-arrow"></i>
                                        </button>
                                    </div>

                                    <form action="{{ $homeShopUrl }}" method="GET" role="search" class="searchbar-box-2 input-group d-xl-flex d-none">
                                        <button class="btn search-icon" type="submit">
                                            <i class="iconly-Search icli"></i>
                                        </button>
                                        <input type="text" name="search" value="{{ $searchQuery ?? '' }}" class="form-control"
                                            placeholder="{{ web_t('header.search_placeholder', 'Search for products') }}">
                                        <button class="btn search-button" type="submit">{{ web_t('header.search', 'Search') }}</button>
                                    </form>

                                    @include('store.partials.language-selector')
                                </div>
                            </div>

                            @if($homeCompanyPhone)
                            <div class="rightside-menu support-sidemenu">
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $homeCompanyPhone) }}" class="support-box">
                                    <div class="support-image">
                                        <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/support.png') }}" class="img-fluid blur-up lazyload"
                                            alt="">
                                    </div>
                                    <div class="support-number">
                                        <h2>{{ $homeCompanyPhone }}</h2>
                                        <h4>{{ web_t('header.support_center', '24/7 Support Center') }}</h4>
                                    </div>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12 position-relative">
                    <div class="main-nav nav-left-align">
                        <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky p-0">
                            <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                <div class="offcanvas-header navbar-shadow">
                                    <h5>Menu</h5>
                                    <button class="btn-close lead" type="button" data-bs-dismiss="offcanvas"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @include('store.partials.navbar')
                                </div>
                            </div>
                        </div>

                        <div class="rightside-menu">
                            <ul class="option-list-2">
                                <li>
                                    <a href="javascript:void(0)" class="header-icon search-box search-icon">
                                        <i class="iconly-Search icli"></i>
                                    </a>
                                </li>

                                <li class="onhover-dropdown">
                                    <a href="{{ route('store.page', ['page' => 'wishlist']) }}" class="header-icon swap-icon" data-store-wishlist-link>
                                        <small class="badge-number badge-light store-wishlist-count {{ $homeWishlistCount > 0 ? '' : 'd-none' }}">{{ $homeWishlistCount }}</small>
                                        <i class="iconly-Heart icli"></i>
                                    </a>

                                    <div class="onhover-div">
                                        <ul class="cart-list">
                                            <li>
                                                <div class="drop-cart">
                                                    <div class="drop-contain">
                                                        <h5>{{ web_t('header.wishlist_empty', 'Your wishlist is empty.') }}</h5>
                                                        <h6>{{ web_t('header.wishlist_empty_hint', 'Save products to review them later.') }}</h6>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li>
                                    <a href="{{ route('store.page', ['page'=>'cart']) }}" class="header-icon bag-icon">
                                        <small class="badge-number badge-light">{{ $homeCartCount }}</small>
                                        <i class="iconly-Bag-2 icli"></i>
                                    </a>
                                </li>
                            </ul>

                            <a href="{{ $storeUser ? route('store.page', ['page' => 'user-dashboard']) : route('store.page', ['page' => 'login']) }}" class="user-box">
                                <span class="header-icon">
                                    <i class="iconly-Profile icli"></i>
                                </span>
                                <div class="user-name">
                                    <h6 class="text-content">{{ $storeUser ? $homeUserRole : 'Hello,' }}</h6>
                                    <h4 class="mt-1">{{ $storeUser?->name ?: 'My Account' }}</h4>
                                </div>
                            </a>

                            <a target="_blank" class="btn mobile-app d-xxl-flex d-none"
                                href="https://play.google.com/store/games?utm_source=apac_med&utm_medium=hasem&utm_content=Oct0121&utm_campaign=Evergreen&pcampaignid=MKT-EDR-apac-in-1003227-med-hasem-py-Evergreen-Oct0121-Text_Search_BKWS-BKWS%7CONSEM_kwid_43700065205026415_creativeid_535350509927_device_c&gclid=Cj0KCQjw8uOWBhDXARIsAOxKJ2H1K3VqdJFHodt0-XSnQzcuOuTP-s2aPBE6lG0QVOf8D5cJBsB-DxQaAkNAEALw_wcB&gclsrc=aw.ds">
                                <div class="mobile-image">
                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/mobile.png') }}" class="img-fluid blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="mobile-name">
                                    <h4>{{ web_t('header.download_app', 'Download App') }}</h4>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
