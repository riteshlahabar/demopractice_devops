@php
    $displayCategories = collect($categories)->values();
@endphp

@foreach($displayCategories as $category)
    @php
        $categoryUrl = route('store.category', ['category' => $category->slug]);
        $categoryImage = $category->storefront_image_url;
    @endphp
    <div>
        <div class="category-box-list">
            <a href="{{ $categoryUrl }}" class="category-name">
                <h4>{{ $category->storefront_name }}</h4>
                <h6>{{ (int) ($category->products_count ?? 0) }} items</h6>
            </a>
            <div class="category-box-view">
                @if($categoryImage)
                    <a href="{{ $categoryUrl }}">
                        <img loading="lazy" decoding="async" src="{{ $categoryImage }}" class="img-fluid blur-up lazyload" alt="{{ $category->storefront_name }}">
                    </a>
                @endif
                <button onclick="location.href='{{ $categoryUrl }}';" class="btn shop-button">
                    <span>Shop Now</span>
                    <i class="fas fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
@endforeach

