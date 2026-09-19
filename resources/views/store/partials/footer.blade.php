@php
    $company = $companySetting ?? null;
    $companyName = filled($company?->company_name) ? trim((string) $company->company_name) : 'Bawaskar Farmer Store';
    $companyIntro = filled($company?->short_intro) ? trim((string) $company->short_intro) : null;
    $companyAddress = filled($company?->address) ? trim((string) $company->address) : null;
    $companyPhone = filled($company?->phone) ? trim((string) $company->phone) : null;
    $companyEmail = filled($company?->email) ? trim((string) $company->email) : null;

    $footerLinkGroups = collect(data_get($homeContent ?? [], 'footerLinks', collect()));
    $footerServices = collect(data_get($homeContent ?? [], 'services', collect()));
    $footerCategories = collect($categories ?? collect())->take(6);
    $shopUrl = route('store.page', ['page' => 'shop-left-sidebar']);

    $footerMediaUrl = static function ($record): ?string {
        $path = data_get($record, 'icon_path') ?: data_get($record, 'image_path');

        if (blank($path)) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    };

    $footerSocials = collect([
        ['url' => $company?->facebook_url, 'icon' => 'fa-brands fa-facebook-f'],
        ['url' => $company?->instagram_url, 'icon' => 'fa-brands fa-instagram'],
        ['url' => $company?->youtube_url, 'icon' => 'fa-brands fa-youtube'],
        ['url' => $company?->google_business_url, 'icon' => 'fa-brands fa-google'],
    ])->filter(fn (array $social): bool => filled($social['url']))->values();
@endphp

    <footer class="section-t-space">
        <div class="container-fluid-lg">
            @if($footerServices->isNotEmpty())
                <div class="service-section">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="service-contain">
                                @foreach($footerServices as $footerService)
                                    <div class="service-box">
                                        <div class="service-image">
                                            <img loading="lazy" decoding="async" src="{{ $footerMediaUrl($footerService) ?: asset('fastkart-store/svg/product.svg') }}" class="blur-up lazyload" alt="{{ data_get($footerService, 'title') }}">
                                        </div>

                                        <div class="service-detail">
                                            <h5>{{ storefront_public_t((string) data_get($footerService, 'title'), 'service') }}</h5>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="main-footer section-b-space section-t-space">
                <div class="row g-md-4 g-3">
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="footer-logo">
                            <div class="theme-logo">
                                <a href="{{ route('store.home') }}">
                                    <span class="bawaskar-store-logo">
                                    <img loading="lazy" decoding="async" src="{{ $company?->logo_url ?: asset('logo/logo.png') }}" alt="{{ $companyName }}" class="bawaskar-store-logo-img">
                                    <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                                </span>
                                </a>
                            </div>

                            <div class="footer-logo-contain">
                                @if($companyIntro)
                                    <p>{{ storefront_public_t($companyIntro, 'footer') }}</p>
                                @endif

                                <ul class="address">
                                    @if($companyAddress)
                                        <li>
                                            <i data-feather="home"></i>
                                            <a href="javascript:void(0)">{{ $companyAddress }}</a>
                                        </li>
                                    @endif
                                    @if($companyEmail)
                                        <li>
                                            <i data-feather="mail"></i>
                                            <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="footer-title">
                            <h4>{{ web_t('nav.categories', 'Categories') }}</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                @forelse($footerCategories as $footerCategory)
                                    <li>
                                        <a href="{{ $footerCategory->slug ? route('store.category', ['category' => $footerCategory->slug]) : $shopUrl }}" class="text-content">{{ $footerCategory->storefront_name }}</a>
                                    </li>
                                @empty
                                    <li>
                                        <a href="{{ $shopUrl }}" class="text-content">{{ web_t('nav.all_products', 'All Products') }}</a>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl col-lg-2 col-sm-3">
                        <div class="footer-title">
                            <h4>{{ web_t('footer.useful_links', 'Useful Links') }}</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                @forelse($footerLinkGroups->get('useful', collect()) as $footerLink)
                                    <li>
                                        <a href="{{ $footerLink->url ?: route('store.home') }}" class="text-content">{{ storefront_public_t($footerLink->title, 'footer') }}</a>
                                    </li>
                                @empty
                                    <li>
                                        <a href="{{ route('store.home') }}" class="text-content">{{ web_t('nav.home', 'Home') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ $shopUrl }}" class="text-content">{{ web_t('nav.shop', 'Shop') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'about-us']) }}" class="text-content">{{ web_t('nav.about_us', 'About Us') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'faq']) }}" class="text-content">{{ web_t('nav.faq', 'FAQ') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'contact-us']) }}" class="text-content">{{ web_t('nav.contact_us', 'Contact Us') }}</a>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-2 col-sm-3">
                        <div class="footer-title">
                            <h4>{{ web_t('footer.help_center', 'Help Center') }}</h4>
                        </div>

                        <div class="footer-contain">
                            <ul>
                                @forelse($footerLinkGroups->get('help', collect()) as $footerLink)
                                    <li>
                                        <a href="{{ $footerLink->url ?: route('store.home') }}" class="text-content">{{ storefront_public_t($footerLink->title, 'footer') }}</a>
                                    </li>
                                @empty
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'user-dashboard']) }}#pills-order" class="text-content">{{ web_t('footer.your_order', 'Your Order') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'user-dashboard']) }}" class="text-content">{{ web_t('footer.your_account', 'Your Account') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'order-tracking']) }}" class="text-content">{{ web_t('nav.track_order', 'Track Order') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="text-content">{{ web_t('footer.your_wishlist', 'Your Wishlist') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ $shopUrl }}" class="text-content">{{ web_t('header.search', 'Search') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('store.page', ['page'=>'faq']) }}" class="text-content">{{ web_t('nav.faq', 'FAQ') }}</a>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="footer-title">
                            <h4>{{ web_t('nav.contact_us', 'Contact Us') }}</h4>
                        </div>

                        <div class="footer-contact">
                            <ul>
                                @if($companyPhone)
                                    <li>
                                        <div class="footer-number">
                                            <i data-feather="phone"></i>
                                            <div class="contact-number">
                                                <h6 class="text-content">{{ web_t('footer.hotline', 'Hotline 24/7 :') }}</h6>
                                                <h5><a href="tel:{{ preg_replace('/[^0-9+]/', '', $companyPhone) }}">{{ $companyPhone }}</a></h5>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                @if($companyEmail)
                                    <li>
                                        <div class="footer-number">
                                            <i data-feather="mail"></i>
                                            <div class="contact-number">
                                                <h6 class="text-content">{{ web_t('footer.email_address', 'Email Address :') }}</h6>
                                                <h5><a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a></h5>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                <li class="social-app">
                                    <h5 class="mb-2 text-content">{{ web_t('footer.download_app', 'Download App :') }}</h5>
                                    <ul>
                                        <li class="mb-0">
                                            <a href="https://play.google.com/store/apps" target="_blank">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/playstore.svg') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>
                                        </li>
                                        <li class="mb-0">
                                            <a href="https://www.apple.com/in/app-store/" target="_blank">
                                                <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/appstore.svg') }}" class="blur-up lazyload"
                                                    alt="">
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sub-footer section-small-space">
                <div class="reserve">
                    <h6 class="text-content">&copy;{{ date('Y') }} {{ $companyName }} {{ web_t('footer.rights_reserved', 'All rights reserved') }}</h6>
                </div>

                <div class="payment">
                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/payment/1.png') }}" class="blur-up lazyload" alt="">
                </div>

                @if($footerSocials->isNotEmpty())
                    <div class="social-link">
                        <h6 class="text-content">{{ web_t('footer.stay_connected', 'Stay connected :') }}</h6>
                        <ul>
                            @foreach($footerSocials as $footerSocial)
                                <li>
                                    <a href="{{ $footerSocial['url'] }}" target="_blank" rel="noopener">
                                        <i class="{{ $footerSocial['icon'] }}"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </footer>
