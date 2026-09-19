@php
    $company = $companySetting ?? null;
    $companyName = filled($company?->company_name) ? trim((string) $company->company_name) : 'Bawaskar Farmer Store';
    $companyIntro = filled($company?->short_intro) ? trim((string) $company->short_intro) : null;
    $companyAddress = filled($company?->address) ? trim((string) $company->address) : null;
    $companyPhone = filled($company?->phone) ? trim((string) $company->phone) : null;
    $companyEmail = filled($company?->email) ? trim((string) $company->email) : null;
    $companyFax = filled($company?->fax) ? trim((string) $company->fax) : null;
    $footerLinks = collect(data_get($homeContent ?? [], 'footerLinks', collect()));
    $footerSocials = collect([
        ['url' => $company?->facebook_url, 'icon' => 'fab fa-facebook-f'],
        ['url' => $company?->google_business_url, 'icon' => 'fab fa-google'],
        ['url' => $company?->instagram_url, 'icon' => 'fab fa-instagram'],
        ['url' => $company?->youtube_url, 'icon' => 'fab fa-youtube'],
    ])->filter(fn (array $social): bool => filled($social['url']))->values();
@endphp

    <footer class="section-t-space footer-section-2 footer-color-2">
        <div class="container-fluid-lg">
            <div class="main-footer">
                <div class="row g-md-4 gy-sm-5">
                    <div class="col-xxl-3 col-xl-4 col-sm-6">
                        <a href="{{ route('store.home') }}" class="foot-logo theme-logo">
                            <span class="bawaskar-store-logo">
                                    <img loading="lazy" decoding="async" src="{{ asset('logo/logo.png') }}" alt="Dr. Bawasakar Technology" class="bawaskar-store-logo-img">
                                    <span class="bawaskar-store-logo-text">Dr. Bawasakar <small>Technology</small></span>
                                </span>
                        </a>
                        @if($companyIntro)
                            <p class="information-text information-text-2">{{ storefront_public_t($companyIntro, 'footer') }}</p>
                        @endif
                        @if($footerSocials->isNotEmpty())
                            <ul class="social-icon">
                                @foreach($footerSocials as $footerSocial)
                                    <li class="light-bg">
                                        <a href="{{ $footerSocial['url'] }}" class="footer-link-color" target="_blank" rel="noopener">
                                            <i class="{{ $footerSocial['icon'] }}"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="col-xxl-2 col-xl-4 col-sm-6">
                        <div class="footer-title">
                            <h4 class="text-white">About Bawaskar Farmer Store</h4>
                        </div>
                        <ul class="footer-list footer-contact footer-list-light">
                            @forelse($footerLinks->get('about', collect()) as $footerLink)
                                <li><a href="{{ $footerLink->url ?: route('store.home') }}" class="light-text">{{ storefront_public_t($footerLink->title, 'footer') }}</a></li>
                            @empty
                                <li><a href="{{ route('store.page', ['page'=>'about-us']) }}" class="light-text">{{ web_t('nav.about_us', 'About Us') }}</a></li>
                                <li><a href="{{ route('store.page', ['page'=>'contact-us']) }}" class="light-text">{{ web_t('nav.contact_us', 'Contact Us') }}</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">{{ web_t('footer.terms_conditions', 'Terms & Conditions') }}</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">{{ web_t('footer.careers', 'Careers') }}</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">Latest Blog</a></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="col-xxl-2 col-xl-4 col-sm-6">
                        <div class="footer-title">
                            <h4 class="text-white">Useful Link</h4>
                        </div>
                        <ul class="footer-list footer-list-light footer-contact">
                            @forelse($footerLinks->get('useful', collect()) as $footerLink)
                                <li><a href="{{ $footerLink->url ?: route('store.home') }}" class="light-text">{{ storefront_public_t($footerLink->title, 'footer') }}</a></li>
                            @empty
                                <li><a href="{{ route('store.page', ['page'=>'user-dashboard']) }}#pills-order" class="light-text">Your Order</a></li>
                                <li><a href="{{ route('store.page', ['page'=>'user-dashboard']) }}" class="light-text">Your Account</a></li>
                                <li><a href="{{ route('store.page', ['page'=>'order-tracking']) }}" class="light-text">Track Orders</a></li>
                                <li><a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="light-text">Your Wishlist</a></li>
                                <li><a href="{{ route('store.page', ['page'=>'faq']) }}" class="light-text">FAQs</a></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="col-xxl-2 col-xl-4 col-sm-6">
                        <div class="footer-title">
                            <h4 class="text-white">{{ web_t('nav.categories', 'Categories') }}</h4>
                        </div>
                        <ul class="footer-list footer-list-light footer-contact">
                            @forelse($footerLinks->get('categories', collect()) as $footerLink)
                                <li><a href="{{ $footerLink->url ?: route('store.home') }}" class="light-text">{{ storefront_public_t($footerLink->title, 'footer') }}</a></li>
                            @empty
                                <li><a href="{{ route('store.home') }}" class="light-text">Fresh Vegetables</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">Hot Spice</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">Brand New Bags</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">New Bakery</a></li>
                                <li><a href="{{ route('store.home') }}" class="light-text">New Grocery</a></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-sm-6">
                        <div class="footer-title">
                            <h4 class="text-white">Store information</h4>
                        </div>
                        <ul class="footer-address footer-contact">
                            @if($companyAddress)
                                <li>
                                    <a href="javascript:void(0)" class="light-text">
                                        <div class="inform-box flex-start-box">
                                            <i data-feather="map-pin"></i>
                                            <p>{{ $companyAddress }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endif

                            @if($companyPhone)
                                <li>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $companyPhone) }}" class="light-text">
                                        <div class="inform-box">
                                            <i data-feather="phone"></i>
                                            <p>{{ web_t('footer.call_us', 'Call us') }}: {{ $companyPhone }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endif

                            @if($companyEmail)
                                <li>
                                    <a href="mailto:{{ $companyEmail }}" class="light-text">
                                        <div class="inform-box">
                                            <i data-feather="mail"></i>
                                            <p>{{ web_t('footer.email_us', 'Email Us') }}: {{ $companyEmail }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endif

                            @if(filled($companyFax))
                                <li>
                                    <a href="javascript:void(0)" class="light-text">
                                        <div class="inform-box">
                                            <i data-feather="printer"></i>
                                            <p>{{ web_t('footer.fax', 'Fax') }}: {{ $companyFax }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sub-footer sub-footer-lite section-b-space section-t-space">
                <div class="left-footer">
                    <p class="light-text">&copy;{{ date('Y') }} {{ $companyName }} {{ web_t('footer.rights_reserved', 'All rights reserved') }}</p>
                </div>

                <ul class="payment-box">
                    <li>
                        <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/paymant/visa.png') }}" class="blur-up lazyload" alt="">
                    </li>
                    <li>
                        <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/paymant/discover.png') }}" alt="" class="blur-up lazyload">
                    </li>
                    <li>
                        <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/paymant/american.png') }}" alt="" class="blur-up lazyload">
                    </li>
                    <li>
                        <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/paymant/master-card.png') }}" alt="" class="blur-up lazyload">
                    </li>
                    <li>
                        <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/icon/paymant/giro-pay.png') }}" alt="" class="blur-up lazyload">
                    </li>
                </ul>
            </div>
        </div>
    </footer>
