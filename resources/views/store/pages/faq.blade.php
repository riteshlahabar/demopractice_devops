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

    @php
        $faqs = collect($faqs ?? []);
        $faqCategories = $faqCategories ?? [];
        $faqPageUrl = route('store.page', ['page' => 'faq']);
        $faqUrl = function (?string $category, ?string $search) use ($faqPageUrl): string {
            $query = array_filter(['faq_category' => $category, 'faq_search' => $search], fn ($value) => filled($value));

            return $query === [] ? $faqPageUrl : $faqPageUrl.'?'.http_build_query($query);
        };
        $faqCategoryLabel = web_t('faq.all_questions', 'All Questions');
        foreach ($faqCategories as $faqCategoryCard) {
            if ($faqCategoryCard['active']) {
                $faqCategoryLabel = storefront_public_t($faqCategoryCard['label'], 'faq_category');
            }
        }
    @endphp

    <!-- Breadcrumb Section Start -->
    <section class="faq-breadcrumb pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>{{ web_t('faq.help_center', 'Help Center') }}</h2>
                        <p>{{ web_t('faq.help_center_text', 'We are glad to have you here looking for an answer. Search your question below, or pick a topic. If you still need help, our support team is one message away.') }}</p>
                        <div class="faq-form-tag">
                            <form action="{{ $faqPageUrl }}" method="GET" role="search">
                                <div class="input-group">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="search" class="form-control" id="exampleFormControlInput1"
                                        name="faq_search" value="{{ $faqSearch ?? '' }}"
                                        placeholder="{{ web_t('faq.search_placeholder', 'Search your question') }}">
                                    @if (($faqCategory ?? null) !== null)
                                        <input type="hidden" name="faq_category" value="{{ $faqCategory }}">
                                    @endif
                                    <div class="dropdown">
                                        <button class="btn btn-md faq-dropdown-button dropdown-toggle" type="button"
                                            id="dropdownMenuButton1" data-bs-toggle="dropdown">{{ $faqCategoryLabel }} <i class="fa-solid fa-angle-down ms-2"></i></button>
                                        <ul class="dropdown-menu faq-dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ $faqUrl(null, $faqSearch ?? null) }}">{{ web_t('faq.all_questions', 'All Questions') }}</a></li>
                                            @foreach ($faqCategories ?? [] as $faqCategoryCard)
                                                <li><a class="dropdown-item" href="{{ $faqUrl($faqCategoryCard['key'], $faqSearch ?? null) }}">{{ storefront_public_t($faqCategoryCard['label'], 'faq_category') }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Faq Question section Start -->
    <section class="faq-contain">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="slider-4-2 product-wrapper">
                        @foreach ($faqCategories as $faqCategoryCard)
                            <div>
                                <a href="{{ $faqCategoryCard['active'] ? $faqUrl(null, $faqSearch ?? null) : $faqUrl($faqCategoryCard['key'], $faqSearch ?? null) }}"
                                    class="faq-top-link{{ $faqCategoryCard['active'] ? ' active' : '' }}">
                                    <div class="faq-top-box">
                                        <div class="faq-box-icon">
                                            <img loading="lazy" decoding="async" src="{{ asset($faqCategoryCard['image']) }}" class="blur-up lazyload"
                                                alt="{{ $faqCategoryCard['label'] }}">
                                        </div>

                                        <div class="faq-box-contain">
                                            <h3>{{ storefront_public_t($faqCategoryCard['label'], 'faq_category') }}</h3>
                                            <p>{{ storefront_public_t($faqCategoryCard['description'], 'faq_category') }}</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Faq Question section End -->

    <!-- Faq Section Start -->
    <section class="faq-box-contain section-b-space">
        <div class="container">
            <div class="row">
                <div class="col-xl-5">
                    <div class="faq-contain">
                        <h2>{{ web_t('faq.title', 'Frequently Asked Questions') }}</h2>
                        @if (filled($faqSearch ?? null))
                            <p>{{ $faqs->count() }} {{ web_t('faq.results_for', 'result(s) for') }} "{{ $faqSearch }}".
                                <a href="{{ $faqUrl($faqCategory ?? null, null) }}" class="theme-color text-decoration-underline">{{ web_t('faq.clear_search', 'Clear search') }}</a>
                            </p>
                        @else
                            <p>{{ web_t('faq.intro', 'We are answering the questions we are asked most often. If you do not find yours here, you can always') }} <a
                                    href="{{ route('store.page', ['page'=>'contact-us']) }}" class="theme-color text-decoration-underline">{{ web_t('faq.contact_support', 'contact our support team.') }}</a></p>
                        @endif
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="faq-accordion">
                        <div class="accordion" id="accordionExample">
                            @forelse ($faqs as $faq)
                                @php($faqItemId = 'faq'.$faq->id)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $faqItemId }}">
                                        <button class="accordion-button{{ $loop->first ? '' : ' collapsed' }}" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $faqItemId }}">
                                            {{ storefront_public_t($faq->question, 'faq_question') }} <i
                                                class="fa-solid fa-angle-down"></i>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $faqItemId }}" class="accordion-collapse collapse{{ $loop->first ? ' show' : '' }}"
                                        data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            @foreach ($faq->answerParagraphs() as $faqParagraph)
                                                <p>{{ storefront_public_t($faqParagraph, 'faq_answer') }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="accordion-item">
                                    <div class="accordion-body">
                                        <p>{{ web_t('faq.no_results', 'No questions match your search. Try another word, or contact our support team.') }}</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Faq Section End -->

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
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>

