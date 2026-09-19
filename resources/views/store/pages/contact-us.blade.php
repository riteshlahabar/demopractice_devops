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
    @php
        $contactCompany = $companySetting ?? null;
        $contactMapUrl = $contactCompany?->mapEmbedUrl();

        // Only the details filled in Company Profile are shown; a blank field
        // simply removes its box.
        $contactBoxes = collect([
            ['key' => 'contact.phone_label', 'label' => 'Phone', 'icon' => 'fa-solid fa-phone', 'value' => trim((string) $contactCompany?->phone), 'href' => null],
            ['key' => 'contact.whatsapp_label', 'label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'value' => trim((string) $contactCompany?->whatsapp), 'href' => null],
            ['key' => 'contact.email_label', 'label' => 'Email', 'icon' => 'fa-solid fa-envelope', 'value' => trim((string) $contactCompany?->email), 'href' => null],
            ['key' => 'contact.address_label', 'label' => 'Address', 'icon' => 'fa-solid fa-location-dot', 'value' => trim((string) $contactCompany?->address), 'href' => null],
        ])->filter(fn (array $box): bool => $box['value'] !== '')
            ->map(function (array $box): array {
                $box['href'] = match ($box['key']) {
                    'contact.phone_label' => 'tel:'.preg_replace('/\s+/', '', $box['value']),
                    'contact.whatsapp_label' => 'https://wa.me/'.preg_replace('/\D+/', '', $box['value']),
                    'contact.email_label' => 'mailto:'.$box['value'],
                    default => null,
                };

                return $box;
            })->values();

        $contactUserName = trim((string) ($storeUser?->name ?? ''));
        $contactNameParts = $contactUserName === '' ? [] : preg_split('/\s+/', $contactUserName, 2);
        $contactUserFirstName = $contactNameParts[0] ?? '';
        $contactUserLastName = $contactNameParts[1] ?? '';
        $contactUserEmail = \App\Models\User::displayEmail($storeUser?->email) ?? '';
        $contactUserPhone = trim((string) ($storeUser?->mobile ?? ''));
    @endphp

    <section class="breadcrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>{{ web_t('nav.contact_us', 'Contact Us') }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('store.home') }}">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">{{ web_t('nav.contact_us', 'Contact Us') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Contact Box Section Start -->
    <section class="contact-box-section">
        <div class="container-fluid-lg">
            <div class="row g-lg-5 g-3">
                <div class="col-lg-6">
                    <div class="left-sidebar-box">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="contact-image">
                                    <img loading="lazy" decoding="async" src="{{ asset('fastkart-store/images/inner-page/contact-us.png') }}"
                                        class="img-fluid blur-up lazyloaded" alt="">
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="contact-title">
                                    <h3>Get In Touch</h3>
                                </div>

                                <div class="contact-detail">
                                    <div class="row g-4">
                                        @foreach ($contactBoxes as $contactBox)
                                            <div class="col-xxl-6 col-lg-12 col-sm-6">
                                                <div class="contact-detail-box">
                                                    <div class="contact-icon">
                                                        <i class="{{ $contactBox['icon'] }}"></i>
                                                    </div>

                                                    <div class="contact-detail-title">
                                                        <h4>{{ web_t($contactBox['key'], $contactBox['label']) }}</h4>
                                                    </div>

                                                    <div class="contact-detail-contain">
                                                        <p>
                                                            @if ($contactBox['href'])
                                                                <a href="{{ $contactBox['href'] }}">{{ $contactBox['value'] }}</a>
                                                            @else
                                                                {{ $contactBox['value'] }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="title d-xxl-none d-block">
                        <h2>{{ web_t('nav.contact_us', 'Contact Us') }}</h2>
                    </div>
                    <div class="right-sidebar-box">
                        @if (session('contact_success'))
                            <div class="alert alert-success" role="alert">{{ session('contact_success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $contactError)
                                        <li>{{ $contactError }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('store.contact') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xxl-6 col-lg-12 col-sm-6">
                                    <div class="mb-md-4 mb-3 custom-form">
                                        <label for="exampleFormControlInput" class="form-label">{{ web_t('contact.first_name', 'First Name') }}</label>
                                        <div class="custom-input">
                                            <input type="text" class="form-control" id="exampleFormControlInput"
                                                name="first_name" value="{{ old('first_name', $contactUserFirstName) }}" required
                                                placeholder="{{ web_t('contact.first_name_placeholder', 'Enter First Name') }}">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-lg-12 col-sm-6">
                                    <div class="mb-md-4 mb-3 custom-form">
                                        <label for="exampleFormControlInput1" class="form-label">{{ web_t('contact.last_name', 'Last Name') }}</label>
                                        <div class="custom-input">
                                            <input type="text" class="form-control" id="exampleFormControlInput1"
                                                name="last_name" value="{{ old('last_name', $contactUserLastName) }}"
                                                placeholder="{{ web_t('contact.last_name_placeholder', 'Enter Last Name') }}">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-lg-12 col-sm-6">
                                    <div class="mb-md-4 mb-3 custom-form">
                                        <label for="exampleFormControlInput2" class="form-label">{{ web_t('contact.email', 'Email Address') }}</label>
                                        <div class="custom-input">
                                            <input type="email" class="form-control" id="exampleFormControlInput2"
                                                name="email" value="{{ old('email', $contactUserEmail) }}" required
                                                placeholder="{{ web_t('contact.email_placeholder', 'Enter Email Address') }}">
                                            <i class="fa-solid fa-envelope"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-lg-12 col-sm-6">
                                    <div class="mb-md-4 mb-3 custom-form">
                                        <label for="exampleFormControlInput3" class="form-label">{{ web_t('contact.phone', 'Phone Number') }}</label>
                                        <div class="custom-input">
                                            <input type="tel" class="form-control" id="exampleFormControlInput3"
                                                name="phone" value="{{ old('phone', $contactUserPhone) }}"
                                                placeholder="{{ web_t('contact.phone_placeholder', 'Enter Your Phone Number') }}" maxlength="10" oninput="javascript: if (this.value.length > this.maxLength) this.value =
                                                this.value.slice(0, this.maxLength);">
                                            <i class="fa-solid fa-mobile-screen-button"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-md-4 mb-3 custom-form">
                                        <label for="exampleFormControlInputSubject" class="form-label">{{ web_t('contact.subject', 'Subject') }}</label>
                                        <div class="custom-input">
                                            <input type="text" class="form-control" id="exampleFormControlInputSubject"
                                                name="subject" value="{{ old('subject') }}"
                                                placeholder="{{ web_t('contact.subject_placeholder', 'What is this about?') }}">
                                            <i class="fa-solid fa-tag"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-md-4 mb-3 custom-form">
                                        <label for="exampleFormControlTextarea" class="form-label">{{ web_t('contact.message', 'Message') }}</label>
                                        <div class="custom-textarea">
                                            <textarea class="form-control" id="exampleFormControlTextarea" name="message" required
                                                placeholder="{{ web_t('contact.message_placeholder', 'Enter Your Message') }}" rows="6">{{ old('message') }}</textarea>
                                            <i class="fa-solid fa-message"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Spam trap: hidden from people, filled in by bots. --}}
                            <div class="d-none" aria-hidden="true">
                                <label for="contact-website">Website</label>
                                <input type="text" id="contact-website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <button type="submit" class="btn btn-animation btn-md fw-bold ms-auto">{{ web_t('contact.send', 'Send Message') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Box Section End -->

    <!-- Map Section Start -->
    @if ($contactMapUrl)
    <section class="map-section">
        <div class="container-fluid p-0">
            <div class="map-box">
                <iframe
                    src="{{ $contactMapUrl }}"
                    style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>
    @endif
    <!-- Map Section End -->

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

    <!-- Wizard js -->
    <script src="{{ asset('fastkart-store/js/wizard.js') }}"></script>

    <!-- Slick js-->
    <script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>

