@php
    $homepageRows = collect(data_get($homeContent ?? [], 'homepageRows', collect()));

    $isProduct = fn ($entry) => $entry instanceof \App\Models\Catalog\Product;

    $entryTitle = function ($entry, $fallback = '') use ($isProduct) {
        if ($isProduct($entry)) {
            $value = $entry->homepage_title ?: $entry->translatedName() ?: $fallback;
            return storefront_public_t($value, 'homepage_entry');
        }

        return storefront_public_t($entry->title ?: $fallback, 'homepage_entry');
    };

    // Text drawn on top of a banner image: an admin-entered banner title only,
    // never the product name.
    $entryHeading = function ($entry) use ($isProduct) {
        $value = $isProduct($entry) ? $entry->homepage_title : $entry->title;

        return filled($value) ? storefront_public_t($value, 'homepage_entry') : null;
    };

    $entrySubtitle = function ($entry) use ($isProduct) {
        if ($isProduct($entry)) {
            return storefront_public_t($entry->homepage_subtitle ?: $entry->sale_badge_text, 'homepage_entry');
        }

        return storefront_public_t($entry->subtitle, 'homepage_entry');
    };

    $entryDescription = function ($entry) use ($isProduct) {
        if ($isProduct($entry)) {
            return storefront_public_t($entry->homepage_description ?: $entry->short_description, 'homepage_entry');
        }

        return storefront_public_t($entry->description, 'homepage_entry');
    };

    $entryImage = function ($entry, string $type = 'main', string $fallback = '') use ($isProduct) {
        if ($isProduct($entry)) {
            $field = match ($type) {
                'mobile' => 'homepage_mobile_image_path',
                'logo' => 'homepage_logo_image_path',
                'offer' => 'homepage_offer_image_path',
                default => 'homepage_image_path',
            };

            $path = $entry->{$field}
                ?: $entry->homepage_image_path
                ?: data_get($entry, 'images.0.path');
        } else {
            $field = match ($type) {
                'mobile' => 'mobile_image_path',
                'logo' => 'logo_image_path',
                'offer' => 'offer_image_path',
                default => 'image_path',
            };

            $path = $entry->{$field} ?? null;

            if ($type === 'offer' && ! $path) {
                $path = $entry->image_path ?? null;
            }
        }

        return $path ? \App\Support\ImageAsset::url($path) : null;
    };

    $entryUrl = function ($entry) use ($isProduct) {
        if ($isProduct($entry)) {
            return $entry->homepage_button_url ?: route('store.product', $entry);
        }

        return $entry->button_url ?: 'javascript:void(0)';
    };

    $entryButton = function ($entry, $default = 'Shop Now') use ($isProduct) {
        if ($isProduct($entry)) {
            return storefront_public_t($entry->homepage_button_text ?: $default, 'homepage_button');
        }

        return storefront_public_t($entry->button_text ?: $default, 'homepage_button');
    };

    $entryDiscount = function ($entry) use ($isProduct) {
        return storefront_public_t($isProduct($entry) ? $entry->homepage_discount_text : $entry->discount_text, 'homepage_entry');
    };

    $entryValidity = function ($entry) use ($isProduct) {
        return storefront_public_t($isProduct($entry) ? $entry->homepage_validity_text : $entry->validity_text, 'homepage_entry');
    };

    $entryCoupon = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_coupon_code : $entry->coupon_code;
    };

    $entryIcon = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_icon_key : $entry->icon_key;
    };

    $entryBg = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_background_color : $entry->background_color;
    };

    $entryColor = function ($entry) use ($isProduct) {
        return $isProduct($entry) ? $entry->homepage_text_color : $entry->text_color;
    };
@endphp

@foreach($homepageRows as $homepageRow)
    @php
        $section = $homepageRow['section'];
        $items = collect($homepageRow['items'] ?? collect());
        $products = collect($homepageRow['products'] ?? collect());
        $entries = $products->isNotEmpty() ? $products : $items;
    @endphp

    <?php if ((string) $section->section_type === 'strip_offer_banner') {
        $stripEntries = $items->concat($products);
        $entry = null;
        $stripImage = null;

        foreach ($stripEntries->sortByDesc('id') as $stripEntry) {
            $image = $entryImage($stripEntry, 'main');

            if (filled($image)) {
                $entry = $stripEntry;
                $stripImage = $image;
                break;
            }
        }

        $entry = $entry ?: $items->first() ?: $products->first();
        $stripImage = $stripImage ?: ($entry ? $entryImage($entry, 'main') : null);
    ?>
        <?php if ($entry) { ?>
            <section class="offer-section" id="home-section-{{ $section->section_key }}">
                <div class="container-fluid-lg">
                    <?php if ($stripImage) { ?>
                        <a href="{{ $entryUrl($entry) }}" class="d-block">
                            <img loading="lazy" decoding="async" src="{{ $stripImage }}" class="img-fluid w-100 rounded-3 blur-up lazyload" style="min-height: 70px; max-height: 125px; object-fit: cover;" alt="{{ $entryTitle($entry, $section->title) }}">
                        </a>
                    <?php } else { ?>
                        <div class="offer-box hover-effect" style="{{ $entryBg($entry) ? 'background-color: '.$entryBg($entry).';' : '' }} {{ $entryColor($entry) ? 'color: '.$entryColor($entry).';' : '' }}">
                            <h2>{{ $entryTitle($entry, $section->title) }} <span>{{ $entryDiscount($entry) }}</span></h2>
                            <button class="btn theme-bg-color text-white" onclick="location.href='{{ $entryUrl($entry) }}';">{{ $entryButton($entry) }}</button>
                        </div>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    <?php continue; } ?>

    @switch($section->section_type)

        @case('hero_slider')
            @if($entries->isNotEmpty())
                <section class="home-section-2 home-section-bg pt-0 overflow-hidden" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid p-0">
                        <div class="slider-animate">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="home-contain rounded-0 p-0">
                                        <img loading="lazy" decoding="async" src="{{ $entryImage($entry, 'main') }}" class="img-fluid bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}">
                                        <div class="home-detail home-big-space p-center-left home-overlay position-relative">
                                            <div class="container-fluid-lg">
                                                @if($entrySubtitle($entry))<h6 class="ls-expanded theme-color text-uppercase">{{ $entrySubtitle($entry) }}</h6>@endif
                                                @if($entryHeading($entry))<h1 class="heding-2">{{ $entryHeading($entry) }}</h1>@endif
                                                @if($entryDescription($entry))<h5 class="text-content">{{ $entryDescription($entry) }}</h5>@endif
                                                <button class="btn theme-bg-color btn-md text-white fw-bold mt-md-4 mt-2 mend-auto" onclick="location.href='{{ $entryUrl($entry) }}';">{{ $entryButton($entry) }} <i class="fa-solid fa-arrow-right icon"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('top_small_banners')
            @if($entries->isNotEmpty())
                <section class="banner-section banner-small ratio_65" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="slider-4-banner no-arrow slick-height">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="banner-contain-3 hover-effect">
                                        <a href="{{ $entryUrl($entry) }}"><img loading="lazy" decoding="async" src="{{ $entryImage($entry, 'main') }}" class="bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}"></a>
                                        <div class="banner-detail p-center-left w-75 banner-p-sm mend-auto">
                                            @if($entrySubtitle($entry))<h5 class="fw-light mb-2">{{ $entrySubtitle($entry) }}</h5>@endif
                                            @if($entryHeading($entry))<h4 class="fw-bold mb-0">{{ $entryHeading($entry) }}</h4>@endif
                                            <button onclick="location.href='{{ $entryUrl($entry) }}';" class="btn shop-now-button mt-3 ps-0 mend-auto theme-color fw-bold">{{ $entryButton($entry) }} <i class="fa-solid fa-chevron-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('category_section')
            <section class="category-section-3" id="home-section-{{ $section->section_key }}">
                <div class="container-fluid-lg">
                    <div class="title"><h2>{{ storefront_public_t($section->title ?: 'Shop By Categories', 'homepage_section') }}</h2></div>
                    <div class="row">
                        <div class="col-12">
                            <div class="category-slider-1 arrow-slider wow fadeInUp">
                                @include('store.partials.category-slider')
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('product_section')
            @if($section->source_type === 'top_selling_products')
                @php($assignedDealProduct = $products->first(fn($product) => (int) ($product->homepage_section_id ?? 0) === (int) $section->id))
                @php($dealProduct = $assignedDealProduct ?: $products->firstWhere('is_deal_timer_product', true) ?: data_get($homeContent ?? [], 'dealTimerProduct'))
                @php($normalProducts = $products->filter(fn($p) => ! $dealProduct || $p->id !== $dealProduct->id)->take(8)->values())
                @php($showDealTimer = $dealProduct && $dealProduct->is_offer_active && $dealProduct->offer_end_at && $dealProduct->offer_end_at->isFuture())
                @include('store.partials.top-selling-section', [
                    'sectionKey' => $section->section_key,
                    'sectionTitle' => storefront_public_t($section->title ?: 'Top Selling Items', 'homepage_section'),
                    'products' => $normalProducts,
                    'dealProduct' => $dealProduct,
                    'showDealTimer' => $showDealTimer,
                ])
            @elseif($products->isNotEmpty())
                <section class="product-section-3" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title">
                            <h2>{{ storefront_public_t($section->title, 'homepage_section') }}</h2>
                            @if($section->subtitle)<span class="title-leaf"><span>{{ storefront_public_t($section->subtitle, 'homepage_section') }}</span></span>@endif
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="slider-7_1 arrow-slider img-slider">
                                    @foreach($products as $product)
                                        @include('store.partials.product-card-4', ['product' => $product])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('coupon_section')
            @if($entries->isNotEmpty())
                <section class="bank-section overflow-hidden" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="title"><h2>{{ storefront_public_t($section->title, 'homepage_section') }}</h2></div>
                        <div class="slider-bank-3 arrow-slider slick-height">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="bank-offer">
                                        <div class="bank-header">
                                            <div class="bank-left w-100">
                                                <div class="bank-image">
                                                    <img loading="lazy" decoding="async" src="{{ $entryImage($entry, 'logo') }}" class="img-fluid" alt="{{ $entryTitle($entry, $section->title) }}">
                                                </div>
                                                <div class="bank-name">
                                                    <h2>{{ $entryTitle($entry, $section->title) }}</h2>
                                                    @if($entryDiscount($entry))<h5 class="discount text-content">{{ $entryDiscount($entry) }}</h5>@endif
                                                    @if($entryValidity($entry))<h5 class="valid text-content">{{ $entryValidity($entry) }}</h5>@endif
                                                </div>
                                            </div>
                                            <div class="bank-right w-100">
                                                <img loading="lazy" decoding="async" src="{{ $entryImage($entry, 'offer') }}" class="img-fluid" alt="{{ $entryTitle($entry, $section->title) }}">
                                            </div>
                                        </div>
                                        @if($entryCoupon($entry))
                                            <div class="bank-footer {{ $loop->iteration === 2 ? 'bank-footer-2' : 'bank-footer-1' }}">
                                                <h4>{{ web_t('coupon.code', 'Code') }} : <input value="{{ $entryCoupon($entry) }}" readonly></h4>
                                                <button class="bank-coupon btn">{{ web_t('coupon.copy_code', 'Copy Code') }}</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('top_selling_section')
            @php($assignedDealProduct = $products->first(fn($product) => (int) ($product->homepage_section_id ?? 0) === (int) $section->id))
            @php($dealProduct = $assignedDealProduct ?: $products->firstWhere('is_deal_timer_product', true) ?: data_get($homeContent ?? [], 'dealTimerProduct'))
            @php($normalProducts = $products->filter(fn($p) => ! $dealProduct || $p->id !== $dealProduct->id)->take(8)->values())
            @php($showDealTimer = $dealProduct && $dealProduct->is_offer_active && $dealProduct->offer_end_at && $dealProduct->offer_end_at->isFuture())
            @include('store.partials.top-selling-section', [
                'sectionKey' => $section->section_key,
                'sectionTitle' => storefront_public_t($section->title ?: 'Top Selling Items', 'homepage_section'),
                'products' => $normalProducts,
                'dealProduct' => $dealProduct,
                'showDealTimer' => $showDealTimer,
            ])
            @break

        @case('offer_section')
            @if($entries->isNotEmpty())
                <section class="banner-section ratio_60" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        @if($section->title)<div class="title"><h2>{{ storefront_public_t($section->title, 'homepage_section') }}</h2></div>@endif
                        <div class="row g-sm-4 g-3">
                            @foreach($entries as $entry)
                                <div class="{{ $section->layout_type === 'full_width_banner' ? 'col-12' : ($section->layout_type === 'two_column_banner' ? 'col-md-6' : ($loop->first && $section->layout_type === 'big_small_banner' ? 'col-lg-8' : 'col-lg-4 col-md-6')) }}">
                                    <div class="banner-contain hover-effect">
                                        <a href="{{ $entryUrl($entry) }}">
                                            <img loading="lazy" decoding="async" src="{{ $entryImage($entry, 'main') }}" class="bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('blog_section')
            @if($entries->isNotEmpty())
                <section class="blog-section" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        @if($section->title)<div class="title"><h2>{{ storefront_public_t($section->title, 'homepage_section') }}</h2></div>@endif
                        <div class="slider-3-blog arrow-slider slick-height">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="blog-box ratio_50">
                                        <div class="blog-box-image">
                                            <a href="{{ $entryUrl($entry) }}">
                                                <img loading="lazy" decoding="async" src="{{ $entryImage($entry, 'main') }}" class="bg-img blur-up lazyload" alt="{{ $entryTitle($entry, $section->title) }}">
                                            </a>
                                        </div>
                                        <div class="blog-detail">
                                            @if($entrySubtitle($entry))<label>{{ $entrySubtitle($entry) }}</label>@endif
                                            <a href="{{ $entryUrl($entry) }}"><h2>{{ $entryTitle($entry, $section->title) }}</h2></a>
                                            @if($entryDescription($entry))<p class="text-content">{{ str($entryDescription($entry))->limit(120) }}</p>@endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break
        @case('service_section')
            @if($entries->isNotEmpty())
                <section class="service-section section-b-space" id="home-section-{{ $section->section_key }}">
                    <div class="container-fluid-lg">
                        <div class="row g-3 row-cols-xxl-5 row-cols-lg-3 row-cols-md-2">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="service-contain-2">
                                        @if($entryIcon($entry))
                                            <svg class="icon-width"><use xlink:href="{{ asset('fastkart-store/svg/svg/service-icon.svg#'.$entryIcon($entry)) }}"></use></svg>
                                        @endif
                                        <div class="service-detail">
                                            <h3>{{ $entryTitle($entry, $section->title) }}</h3>
                                            <h6 class="text-content">{{ $entrySubtitle($entry) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('video_section')
            @include('store.partials.homepage-video-section', [
                'videoSection' => $section,
                'videoItems' => $items,
                'videoLayout' => $section->layout_type,
            ])
            @break

    @endswitch
@endforeach
<script>
(function () {
    function updateHomepageDealTimer(timer) {
        var endAt = timer.dataset.endAt;
        if (!endAt) {
            return;
        }

        var endTime = new Date(endAt).getTime();
        if (Number.isNaN(endTime)) {
            return;
        }

        function render() {
            var diff = Math.max(0, endTime - Date.now());
            var totalSeconds = Math.floor(diff / 1000);
            var days = Math.floor(totalSeconds / 86400);
            var hours = Math.floor((totalSeconds % 86400) / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;

            var dayNode = timer.querySelector('.days h5, .days h6');
            var hourNode = timer.querySelector('.hours h5, .hours h6');
            var minuteNode = timer.querySelector('.minutes h5, .minutes h6');
            var secondNode = timer.querySelector('.seconds h5, .seconds h6');

            if (dayNode) dayNode.textContent = days;
            if (hourNode) hourNode.textContent = hours;
            if (minuteNode) minuteNode.textContent = minutes;
            if (secondNode) secondNode.textContent = seconds;
        }

        render();
        window.setInterval(render, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.homepage-deal-timer[data-end-at]').forEach(updateHomepageDealTimer);
        });
    } else {
        document.querySelectorAll('.homepage-deal-timer[data-end-at]').forEach(updateHomepageDealTimer);
    }
})();
</script>





