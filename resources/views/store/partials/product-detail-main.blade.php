{{-- Uses the original Fastkart product-left-thumbnail structure and classes. --}}
@php
    $displayName = $product->translatedName();
    $shortDescription = trim((string) $product->short_description);
    $audience = $storeAudience ?? 'customer';
    $activeVariants = collect($product->variants ?? collect())->where('is_active', true)->values();
    $selectedVariant = $activeVariants->firstWhere('is_default', true) ?: $activeVariants->first();
    $price = $selectedVariant ? $selectedVariant->priceFor($audience) : (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);
    $mrp = (float) ($selectedVariant?->mrp ?? $product->mrp);
    $unitsPerCase = $selectedVariant ? max(1, (float) $selectedVariant->units_per_case) : 1;
    $availableStock = $selectedVariant ? (float) $selectedVariant->available_stock : (float) $product->available_stock;
    $availableCases = $unitsPerCase > 0 ? floor($availableStock / $unitsPerCase) : 0;
    $discount = $mrp > $price && $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
    $isOutOfStock = $availableStock <= 0 || ($audience === 'dealer' && $selectedVariant && $availableCases < 1);
    $detailImages = $product->images->filter(fn ($image) => filled($image->url))->values();
    if ($detailImages->isEmpty()) $detailImages = collect([(object) ['url' => $product->storefront_image_url]]);
    $detailMedia = collect($product->media ?? collect())->where('is_active', true)->values();
    $company = $companySetting ?? null;
    $trending = collect($trendingProducts ?? collect());
    $quantityLabel = $audience === 'dealer' && $selectedVariant ? 'Case Quantity' : 'Retail Pack Quantity';
@endphp

<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain">
        <h2>{{ $displayName }}</h2>
        <nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('store.home') }}"><i class="fa-solid fa-house"></i></a></li>
            @if($product->category)<li class="breadcrumb-item"><a href="{{ route('store.category', ['category' => $product->category->slug]) }}">{{ $product->category->storefront_name }}</a></li>@endif
            <li class="breadcrumb-item active">{{ $displayName }}</li>
        </ol></nav>
    </div></div></div></div>
</section>

<section class="product-section" data-product-detail-root>
    <div class="container-fluid-lg"><div class="row">
        <div class="col-xxl-9 col-xl-8 col-lg-7 wow fadeInUp">
            <div class="row g-4">
                <div class="col-xl-6 wow fadeInUp">
                    <div class="product-left-box"><div class="row g-2">
                        <div class="col-xxl-10 col-lg-12 col-md-10 order-xxl-2 order-lg-1 order-md-2">
                            <div class="product-main-2 no-arrow">
                                @foreach($detailImages as $image)
                                    <div><div class="slider-image"><img loading="lazy" decoding="async" src="{{ $image->url }}" class="img-fluid image_zoom_cls-{{ $loop->index }} blur-up lazyload" alt="{{ $displayName }}"></div></div>
                                @endforeach
                                @foreach($detailMedia as $media)
                                    <div><div class="slider-image">
                                        @if($media->source_type === 'youtube' && $media->embed_url)
                                            <div class="ratio ratio-1x1"><iframe src="{{ $media->embed_url }}" title="{{ $media->title ?: $displayName }}" allowfullscreen></iframe></div>
                                        @else
                                            <video class="img-fluid w-100" controls preload="metadata" @if($media->thumbnail_url) poster="{{ $media->thumbnail_url }}" @endif><source src="{{ $media->url }}"></video>
                                        @endif
                                    </div></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                            <div class="left-slider-image-2 left-slider no-arrow slick-top">
                                @foreach($detailImages as $image)
                                    <div><div class="sidebar-image"><img loading="lazy" decoding="async" src="{{ $image->url }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}"></div></div>
                                @endforeach
                                @foreach($detailMedia as $media)
                                    <div><div class="sidebar-image position-relative"><img loading="lazy" decoding="async" src="{{ $media->thumbnail_url ?: $product->storefront_image_url }}" class="img-fluid blur-up lazyload" alt="{{ $media->title ?: $displayName }}"><i class="fa-solid fa-play position-absolute top-50 start-50 translate-middle text-white"></i></div></div>
                                @endforeach
                            </div>
                        </div>
                    </div></div>
                </div>

                <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="right-box-contain">
                        @if($discount > 0)<h6 class="offer-top">{{ $discount }}% Off</h6>@endif
                        <h2 class="name">{{ $displayName }}</h2>
                        @if($shortDescription !== '')<div class="product-contain"><p>{{ $shortDescription }}</p></div>@endif
                        <div class="price-rating"><h3 class="theme-color price">
                            Rs. <span data-detail-price>{{ number_format($price, 2) }}</span>
                            <del class="text-content" data-detail-mrp @if($mrp <= $price) style="display:none" @endif>Rs. <span>{{ number_format($mrp, 2) }}</span></del>
                        </h3></div>
                        <div class="product-contain mb-3"><p data-detail-price-note>
                            @if($audience === 'dealer' && $selectedVariant)
                                Dealer rate per retail pack (GST inclusive). One case = {{ number_format($unitsPerCase, 0) }} retail packs; case rate Rs. {{ number_format($price * $unitsPerCase, 2) }}.
                            @else
                                Price per retail pack.
                            @endif
                        </p></div>

                        @if($activeVariants->isNotEmpty())
                            <div class="product-package mb-3">
                                <div class="product-title"><h4>Size / Pack</h4></div>
                                <ul class="select-package">
                                    @foreach($activeVariants as $variant)
                                        @php
                                            $variantPrice = $variant->priceFor($audience);
                                            $variantMrp = (float) ($variant->mrp ?? $product->mrp);
                                            $variantUnits = max(1, (float) $variant->units_per_case);
                                            $variantStock = (float) $variant->available_stock;
                                            $variantCases = floor($variantStock / $variantUnits);
                                        @endphp
                                        <li><a href="javascript:void(0)" class="{{ $selectedVariant?->id === $variant->id ? 'active' : '' }}" data-product-variant-option data-variant-id="{{ $variant->id }}" data-price="{{ $variantPrice }}" data-mrp="{{ $variantMrp }}" data-units="{{ $variantUnits }}" data-stock="{{ $variantStock }}" data-cases="{{ $variantCases }}" data-label="{{ $variant->display_name }}">{{ $variant->display_name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="pickup-box"><div class="product-info"><ul class="product-info-list product-info-list-2">
                            <li>SKU : <a href="javascript:void(0)">{{ $selectedVariant?->variant_sku ?: $product->sku }}</a></li>
                            <li>{{ web_t('product.category', 'Category') }} : <a href="javascript:void(0)">{{ data_get($product, 'category.storefront_name') ?: web_t('product.fallback', 'Product') }}</a></li>
                            <li>Brand : <a href="javascript:void(0)">{{ data_get($product, 'brand.name') ?: 'Bawaskar' }}</a></li>
                            <li>Available Stock : <a href="javascript:void(0)" data-detail-stock>{{ number_format($availableStock, 0) }} retail packs{{ $audience === 'dealer' && $selectedVariant ? ' / ' . number_format($availableCases, 0) . ' full cases' : '' }}</a></li>
                            <li>{{ web_t('product.status', 'Status') }} : <a href="javascript:void(0)" data-detail-status>{{ $isOutOfStock ? web_t('product.out_of_stock', 'Out of Stock') : web_t('product.in_stock', 'In Stock') }}</a></li>
                        </ul></div></div>

                        <div class="note-box product-package">
                            <form method="POST" action="{{ route('store.cart.add') }}" class="row g-2 align-items-end" data-store-cart-add data-product-detail-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="variant_id" value="{{ $selectedVariant?->id }}" data-selected-variant-input>
                                <div class="col-sm-4"><label class="form-label">{{ $quantityLabel }}</label><input type="number" class="form-control" name="quantity" value="1" min="1" step="1" data-product-quantity></div>
                                <div class="col-sm-8"><button type="submit" class="btn btn-md bg-dark cart-button text-white w-100" data-detail-cart-button @disabled($isOutOfStock)>{{ $isOutOfStock ? web_t('product.out_of_stock', 'Out of Stock') : web_t('product.add_to_cart', 'Add To Cart') }}</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                @php
                    $additionalInformation = collect($product->additional_info ?? [])
                        ->filter(fn (array $row): bool => filled($row['label'] ?? null) || filled($row['value'] ?? null));
                @endphp

                <div class="col-12">
                    <div class="product-section-box">
                        <ul class="nav nav-tabs custom-nav" id="productDetailTab" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button></li>
                            @if($additionalInformation->isNotEmpty())<li class="nav-item" role="presentation"><button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Additional info</button></li>@endif
                            @if($product->care_instructions)<li class="nav-item" role="presentation"><button class="nav-link" id="care-tab" data-bs-toggle="tab" data-bs-target="#care" type="button" role="tab">Care Instructions</button></li>@endif
                        </ul>
                        <div class="tab-content custom-tab" id="productDetailTabContent">
                            <div class="tab-pane fade show active" id="description" role="tabpanel"><div class="product-description">
                                @include('store.partials.product-detail-description-banner', ['position' => 'before'])
                                @if($product->description)<div class="nav-desh"><p>{!! nl2br(e($product->translatedDescription())) !!}</p></div>@endif
                                @include('store.partials.product-detail-description-banner', ['position' => 'middle'])
                                @if($product->benefits)<div class="nav-desh"><div class="desh-title"><h5>Benefits:</h5></div><p>{!! nl2br(e($product->benefits)) !!}</p></div>@endif
                                @if($product->usage_instructions)<div class="nav-desh"><div class="desh-title"><h5>Usage Instructions:</h5></div><p>{!! nl2br(e($product->usage_instructions)) !!}</p></div>@endif
                                @if($product->crop_information)<div class="nav-desh"><div class="desh-title"><h5>Crop Information:</h5></div><p>{!! nl2br(e($product->crop_information)) !!}</p></div>@endif
                                @include('store.partials.product-detail-description-banner', ['position' => 'after'])
                            </div></div>
                            @if($additionalInformation->isNotEmpty())
                                <div class="tab-pane fade" id="info" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table info-table">
                                            <tbody>
                                                @foreach($additionalInformation as $infoRow)
                                                    <tr>
                                                        @if(filled($infoRow['label'] ?? null))
                                                            <td>{{ $infoRow['label'] }}</td>
                                                            <td>{{ $infoRow['value'] }}</td>
                                                        @else
                                                            <td colspan="2">{{ $infoRow['value'] }}</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            @if($product->care_instructions)<div class="tab-pane fade" id="care" role="tabpanel"><div class="information-box"><p>{!! nl2br(e($product->care_instructions)) !!}</p></div></div>@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-4 col-lg-5 d-none d-lg-block wow fadeInUp">
            <div class="right-sidebar-box">
                <div class="vendor-box">
                    <div class="vendor-contain">
                        <div class="vendor-image"><img loading="lazy" decoding="async" src="{{ $company?->logo_url ?: asset('fastkart-store/images/product/vendor.png') }}" class="blur-up lazyload" alt="{{ $company?->company_name ?: 'Bawaskar Technology' }}"></div>
                        <div class="vendor-name"><h5 class="fw-500">{{ $company?->company_name ?: 'Bawaskar Technology' }}</h5></div>
                    </div>
                    @if($company?->short_intro)<p class="vendor-detail">{{ $company->short_intro }}</p>@endif
                    <div class="vendor-list"><ul>
                        @if($company?->address)<li><div class="address-contact"><i data-feather="map-pin"></i><h5>Address: <span class="text-content">{{ $company->address }}</span></h5></div></li>@endif
                        @if($company?->phone)<li><div class="address-contact"><i data-feather="headphones"></i><h5>Contact Seller: <span class="text-content">{{ $company->phone }}</span></h5></div></li>@endif
                        @if($company?->email)<li><div class="address-contact"><i data-feather="mail"></i><h5>Email: <span class="text-content">{{ $company->email }}</span></h5></div></li>@endif
                        @if($company?->gst_number)<li><div class="address-contact"><i data-feather="file-text"></i><h5>GST: <span class="text-content">{{ $company->gst_number }}</span></h5></div></li>@endif
                    </ul></div>
                </div>

                @if($trending->isNotEmpty())
                    <div class="pt-25"><div class="category-menu">
                        <h3>Trending Products</h3>
                        <ul class="product-list product-right-sidebar border-0 p-0">
                            @foreach($trending as $trendingProduct)
                                @php
                                    $trendingVariant = $trendingProduct->mainVariant();
                                    $trendingPrice = $trendingVariant ? $trendingVariant->priceFor($audience) : (float) ($audience === 'dealer' ? $trendingProduct->dealer_price : $trendingProduct->customer_price);
                                @endphp
                                <li class="{{ $loop->last ? 'mb-0' : '' }}"><div class="offer-product">
                                    <a href="{{ route('store.product', ['product' => $trendingProduct->id]) }}" class="offer-image"><img loading="lazy" decoding="async" src="{{ $trendingProduct->storefront_image_url }}" class="img-fluid blur-up lazyload" alt="{{ $trendingProduct->translatedName() }}"></a>
                                    <div class="offer-detail"><div><a href="{{ route('store.product', ['product' => $trendingProduct->id]) }}"><h6 class="name">{{ $trendingProduct->translatedName() }}</h6></a><span>{{ $trendingVariant?->display_name ?: (data_get($trendingProduct, 'unit.short_name') ?: 'Pack') }}</span><h6 class="price theme-color">Rs. {{ number_format($trendingPrice, 2) }}</h6></div></div>
                                </div></li>
                            @endforeach
                        </ul>
                    </div></div>
                @endif
            </div>
        </div>
    </div></div>
</section>

@if(collect($relatedProducts)->isNotEmpty())
    <section class="product-list-section section-b-space"><div class="container-fluid-lg"><div class="title"><h2>Related Products</h2></div><div class="slider-6_1 product-wrapper">
        @foreach($relatedProducts as $relatedProduct) @include('store.partials.product-card-4', ['product' => $relatedProduct]) @endforeach
    </div></div></section>
@endif

<div class="sticky-bottom-cart" data-product-sticky-cart>
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="cart-content">
        <div class="product-image"><img loading="lazy" decoding="async" src="{{ $product->storefront_image_url }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}"><div class="content"><h5>{{ $displayName }}</h5><h6>Rs. <span data-sticky-price>{{ number_format($price, 2) }}</span><del class="text-danger" data-sticky-mrp @if($mrp <= $price) style="display:none" @endif>Rs. <span>{{ number_format($mrp, 2) }}</span></del></h6></div></div>
        <form method="POST" action="{{ route('store.cart.add') }}" class="selection-section" data-store-cart-add data-product-sticky-cart-form>
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="variant_id" value="{{ $selectedVariant?->id }}" data-selected-variant-input>
            @if($activeVariants->isNotEmpty())<div class="form-group mb-0"><select class="form-control form-select" data-sticky-variant-select>@foreach($activeVariants as $variant)<option value="{{ $variant->id }}" @selected($selectedVariant?->id === $variant->id)>{{ $variant->display_name }}</option>@endforeach</select></div>@endif
            <div class="cart_qty qty-box product-qty m-0"><div class="input-group h-100"><button type="button" class="qty-left-minus" data-detail-quantity-minus><i class="fa fa-minus"></i></button><input class="form-control input-number qty-input" type="number" name="quantity" value="1" min="1" step="1" data-product-quantity><button type="button" class="qty-right-plus" data-detail-quantity-plus><i class="fa fa-plus"></i></button></div></div>
        </form>
        <div class="add-btn"><a class="btn theme-bg-color text-white wishlist-btn" href="{{ route('store.page', ['page'=>'wishlist']) }}"><i class="fa fa-bookmark"></i> Wishlist</a><button class="btn theme-bg-color text-white" type="button" data-sticky-add-button @disabled($isOutOfStock)><i class="fas fa-shopping-cart"></i> <span>{{ $isOutOfStock ? 'Out of Stock' : 'Add To Cart' }}</span></button></div>
    </div></div></div></div></div>
</div>

<script>
(function () {
    function initProductDetail() {
        var root = document.querySelector('[data-product-detail-root]');
        var sticky = document.querySelector('[data-product-sticky-cart]');
        if (!root || !sticky) return;
        var audience = @json($audience);
        var options = Array.from(root.querySelectorAll('[data-product-variant-option]'));
        var forms = Array.from(document.querySelectorAll('[data-product-detail-cart-form], [data-product-sticky-cart-form]'));
        var quantities = Array.from(document.querySelectorAll('[data-product-quantity]'));
        var stickySelect = sticky.querySelector('[data-sticky-variant-select]');
        function money(value) { return Number(value || 0).toFixed(2); }
        function selectVariant(id) {
            var option = options.find(function (item) { return String(item.dataset.variantId) === String(id); }) || options[0];
            if (!option) return;
            options.forEach(function (item) { item.classList.toggle('active', item === option); });
            forms.forEach(function (form) { var input = form.querySelector('[data-selected-variant-input]'); if (input) input.value = option.dataset.variantId; });
            if (stickySelect) stickySelect.value = option.dataset.variantId;
            var price = Number(option.dataset.price || 0), mrp = Number(option.dataset.mrp || 0), units = Math.max(1, Number(option.dataset.units || 1)), stock = Number(option.dataset.stock || 0), cases = Number(option.dataset.cases || 0);
            var out = stock <= 0 || (audience === 'dealer' && cases < 1);
            document.querySelectorAll('[data-detail-price], [data-sticky-price]').forEach(function (target) { target.textContent = money(price); });
            document.querySelectorAll('[data-detail-mrp], [data-sticky-mrp]').forEach(function (target) { target.style.display = mrp > price ? '' : 'none'; var amount = target.querySelector('span'); if (amount) amount.textContent = money(mrp); });
            var note = root.querySelector('[data-detail-price-note]'); if (note) note.textContent = audience === 'dealer' ? 'Dealer rate per retail pack (GST inclusive). One case = ' + units.toFixed(0) + ' retail packs; case rate Rs. ' + money(price * units) + '.' : 'Price per retail pack.';
            var stockTarget = root.querySelector('[data-detail-stock]'); if (stockTarget) stockTarget.textContent = stock.toFixed(0) + ' retail packs' + (audience === 'dealer' ? ' / ' + cases.toFixed(0) + ' full cases' : '');
            var status = root.querySelector('[data-detail-status]'); if (status) status.textContent = out ? 'Out of Stock' : 'In Stock';
            document.querySelectorAll('[data-detail-cart-button], [data-sticky-add-button]').forEach(function (button) { button.disabled = out; var label = button.querySelector('span'); if (label) label.textContent = out ? 'Out of Stock' : 'Add To Cart'; else button.textContent = out ? 'Out of Stock' : 'Add To Cart'; });
        }
        options.forEach(function (option) { option.addEventListener('click', function () { selectVariant(option.dataset.variantId); }); });
        if (stickySelect) stickySelect.addEventListener('change', function () { selectVariant(stickySelect.value); });
        quantities.forEach(function (input) { input.addEventListener('input', function () { quantities.forEach(function (other) { if (other !== input) other.value = input.value; }); }); });
        var minus = sticky.querySelector('[data-detail-quantity-minus]'), plus = sticky.querySelector('[data-detail-quantity-plus]');
        if (minus) minus.addEventListener('click', function () { var input = quantities[quantities.length - 1]; input.value = Math.max(1, Number(input.value || 1) - 1); input.dispatchEvent(new Event('input')); });
        if (plus) plus.addEventListener('click', function () { var input = quantities[quantities.length - 1]; input.value = Math.max(1, Number(input.value || 1) + 1); input.dispatchEvent(new Event('input')); });
        var addButton = sticky.querySelector('[data-sticky-add-button]'); if (addButton) addButton.addEventListener('click', function () { var form = sticky.querySelector('[data-product-sticky-cart-form]'); if (form) form.requestSubmit(); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initProductDetail); else initProductDetail();
})();
</script>
