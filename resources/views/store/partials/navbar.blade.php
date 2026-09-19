@php
    $navigation = $storefrontNavigation ?? [];
    $navCategories = collect(data_get($navigation, 'categories', collect()));
    $navProductTypes = collect(data_get($navigation, 'productTypes', collect()));
    $navFeaturedProducts = collect(data_get($navigation, 'featuredProducts', collect()));
    $navHomeSections = collect(data_get($homeContent ?? [], 'productSections', collect()))
        ->map(fn ($entry) => data_get($entry, 'section'))
        ->filter(fn ($section) => $section && $section->title)
        ->values();

    if ($navHomeSections->isEmpty()) {
        $navHomeSections = collect(data_get($homeContent ?? [], 'sections', collect()))
            ->values()
            ->filter(fn ($section) => $section && $section->title)
            ->values();
    }

    $shopUrl = route('store.page', ['page' => 'shop-left-sidebar']);
@endphp

<ul class="navbar-nav">
    <li class="nav-item dropdown">
        <a class="nav-link ps-0 dropdown-toggle" href="{{ route('store.home') }}" data-bs-toggle="dropdown">{{ web_t('nav.home', 'Home') }}</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('store.home') }}">{{ web_t('nav.home', 'Home') }}</a></li>
            @forelse($navHomeSections->take(10) as $homeSection)
                <li>
                    <a class="dropdown-item" href="{{ route('store.home') }}#home-section-{{ $homeSection->section_key }}">
                        {{ $homeSection->title }}
                    </a>
                </li>
            @empty
                <li><a class="dropdown-item" href="{{ route('store.home') }}#products">{{ web_t('nav.featured_products', 'Featured Products') }}</a></li>
            @endforelse
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ $shopUrl }}" data-bs-toggle="dropdown">{{ web_t('nav.categories', 'Categories') }}</a>
        <ul class="dropdown-menu">
            @forelse($navCategories as $category)
                @php
                    $categoryUrl = $category->slug ? route('store.category', ['category' => $category->slug]) : $shopUrl;
                    $children = collect($category->children ?? [])->take(5);
                @endphp
                <li>
                    <a class="dropdown-item d-flex justify-content-between" href="{{ $categoryUrl }}">
                        <span>{{ $category->storefront_name }}</span>
                        <small>{{ (int) ($category->products_count ?? 0) }}</small>
                    </a>
                </li>
                @foreach($children as $childCategory)
                    <li>
                        <a class="dropdown-item ps-4 d-flex justify-content-between" href="{{ $childCategory->slug ? route('store.category', ['category' => $childCategory->slug]) : $shopUrl }}">
                            <span>{{ $childCategory->storefront_name }}</span>
                            <small>{{ (int) ($childCategory->products_count ?? 0) }}</small>
                        </a>
                    </li>
                @endforeach
            @empty
                <li><a class="dropdown-item" href="{{ $shopUrl }}">{{ web_t('nav.all_categories', 'All Categories') }}</a></li>
            @endforelse
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ $shopUrl }}" data-bs-toggle="dropdown">{{ web_t('nav.products', 'Products') }}</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ $shopUrl }}">{{ web_t('nav.all_products', 'All Products') }}</a></li>
            @foreach($navProductTypes as $productType)
                <li>
                    <a class="dropdown-item d-flex justify-content-between" href="{{ route('store.page', ['page' => 'shop-left-sidebar', 'product_type' => $productType['slug']]) }}">
                        <span>{{ storefront_public_t($productType['name'], 'product_type') }}</span>
                        <small>{{ (int) ($productType['products_count'] ?? 0) }}</small>
                    </a>
                </li>
            @endforeach
        </ul>
    </li>

    <li class="nav-item dropdown dropdown-mega">
        <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown">{{ web_t('nav.mega_menu', 'Mega Menu') }}</a>
        <div class="dropdown-menu dropdown-menu-2 dropdown-menu-left">
            <div class="row">
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">{{ web_t('nav.categories', 'Categories') }}</h5>
                    @forelse($navCategories->take(6) as $category)
                        <a class="dropdown-item" href="{{ $category->slug ? route('store.category', ['category' => $category->slug]) : $shopUrl }}">{{ $category->storefront_name }}</a>
                    @empty
                        <a class="dropdown-item" href="{{ $shopUrl }}">{{ web_t('nav.all_categories', 'All Categories') }}</a>
                    @endforelse
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">Product Types</h5>
                    @foreach($navProductTypes->take(6) as $productType)
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'shop-left-sidebar', 'product_type' => $productType['slug']]) }}">{{ storefront_public_t($productType['name'], 'product_type') }}</a>
                    @endforeach
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">{{ web_t('nav.featured_products', 'Featured Products') }}</h5>
                    @forelse($navFeaturedProducts->take(6) as $product)
                        <a class="dropdown-item" href="{{ route('store.product', ['product' => $product->getKey()]) }}">{{ $product->translatedName() }}</a>
                    @empty
                        <a class="dropdown-item" href="{{ $shopUrl }}">{{ web_t('nav.all_products', 'All Products') }}</a>
                    @endforelse
                </div>
                <div class="dropdown-column col-xl-3">
                    <h5 class="dropdown-header">{{ $storeUser ? 'My Account' : 'Customer' }}</h5>
                    @if($storeUser)
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}">{{ web_t('nav.dashboard', 'Dashboard') }}</a>
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}#pills-order">{{ web_t('nav.recent_order', 'Recent Order') }}</a>
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'order-tracking']) }}">{{ web_t('nav.order_tracking', 'Order Tracking') }}</a>
                    @else
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'login']) }}">{{ web_t('nav.customer_login', 'Customer Login') }}</a>
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'sign-up']) }}">{{ web_t('nav.customer_register', 'Customer Register') }}</a>
                        <a class="dropdown-item" href="{{ route('store.page', ['page' => 'order-tracking']) }}">{{ web_t('nav.order_tracking', 'Order Tracking') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ $storeUser ? route('store.page', ['page' => 'user-dashboard']) : route('store.page', ['page' => 'login']) }}" data-bs-toggle="dropdown">{{ $storeUser?->role === 'dealer' ? web_t('nav.dealer_account', 'Dealer Account') : web_t('nav.dealer', 'Dealer') }}</a>
        <ul class="dropdown-menu">
            @if($storeUser?->role === 'dealer')
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}">{{ web_t('nav.dealer_dashboard', 'Dealer Dashboard') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'user-dashboard']) }}#pills-order">{{ web_t('nav.recent_order', 'Recent Order') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'order-tracking']) }}">{{ web_t('nav.track_order', 'Track Order') }}</a></li>
            @else
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'login', 'role' => 'dealer']) }}">{{ web_t('nav.dealer_login', 'Dealer Login') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'sign-up', 'role' => 'dealer']) }}">{{ web_t('nav.dealer_registration', 'Dealer Registration') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'order-tracking']) }}">{{ web_t('nav.track_order', 'Track Order') }}</a></li>
            @endif
        </ul>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('store.page', ['page' => 'about-us']) }}" data-bs-toggle="dropdown">{{ web_t('nav.about_us', 'About Us') }}</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'about-us']) }}">{{ web_t('nav.company_profile', 'Company Profile') }}</a></li>
            <li><a class="dropdown-item" href="{{ route('store.page', ['page' => 'faq']) }}">{{ web_t('nav.faq', 'FAQ') }}</a></li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link no-dropdown-arrow" href="{{ route('store.page', ['page' => 'contact-us']) }}">{{ web_t('nav.contact_us', 'Contact Us') }}</a>
    </li>
</ul>



