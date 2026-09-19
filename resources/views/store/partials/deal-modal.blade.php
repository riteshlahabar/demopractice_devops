@php
    $dealProducts = collect(data_get($storefrontNavigation ?? [], 'dealProducts', collect()));
    $dealAudience = $storeAudience ?? 'customer';
@endphp
@if ($dealProducts->isNotEmpty())
    <div class="modal fade theme-modal deal-modal" id="deal-box" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title w-100" id="deal_today">{{ web_t('deal.title', 'Deal Today') }}</h5>
                        <p class="mt-1 text-content">{{ web_t('deal.subtitle', 'Recommended deals for you.') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="deal-offer-box">
                        <ul class="deal-offer-list">
                            @foreach ($dealProducts as $dealProduct)
                                @php
                                    $dealUrl = route('store.product', ['product' => $dealProduct->id]);
                                    $dealName = $dealProduct->translatedName();
                                    $dealVariant = $dealProduct->mainVariant();
                                    $dealPrice = $dealVariant ? $dealVariant->priceFor($dealAudience) : (float) ($dealAudience === 'dealer' ? $dealProduct->dealer_price : $dealProduct->customer_price);
                                    $dealMrp = (float) ($dealVariant?->mrp ?? $dealProduct->mrp);
                                    $dealUnit = trim((string) ($dealVariant?->display_name ?: (data_get($dealProduct, 'unit.short_name') ?: data_get($dealProduct, 'unit.name'))));
                                @endphp
                                <li class="list-{{ ($loop->index % 3) + 1 }}">
                                    <div class="deal-offer-contain">
                                        <a href="{{ $dealUrl }}" class="deal-image">
                                            <img loading="lazy" decoding="async" src="{{ $dealProduct->storefront_image_url }}" class="blur-up lazyload"
                                                alt="{{ $dealName }}">
                                        </a>

                                        <a href="{{ $dealUrl }}" class="deal-contain">
                                            <h5>{{ $dealName }}</h5>
                                            <h6>₹{{ number_format($dealPrice, 2) }}@if ($dealMrp > $dealPrice) <del>₹{{ number_format($dealMrp, 2) }}</del>@endif @if ($dealUnit !== '')<span>{{ $dealUnit }}</span>@endif</h6>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
