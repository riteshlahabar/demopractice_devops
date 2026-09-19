@php
    // Admin picks where the banner sits inside the Description tab; anything
    // unset keeps the original behaviour of showing it at the end.
    $bannerPosition = $product->detail_banner_position ?: 'after';
@endphp
@if($product->detail_banner_image && $bannerPosition === $position)
    <div class="banner-contain nav-desh">
        <a href="{{ $product->detail_banner_url ?: 'javascript:void(0)' }}">
            <img loading="lazy" decoding="async" src="{{ asset($product->detail_banner_image) }}" class="img-fluid blur-up lazyload" alt="{{ $displayName }}">
        </a>
    </div>
@endif
