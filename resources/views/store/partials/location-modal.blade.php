{{-- Header "Your Location" modal (Fastkart markup). Districts from admin Storefront -> Delivery Areas via StorefrontDeliveryLocationContract; search + select in public/js/delivery-location.js. --}}
@php
    $deliveryAreas = $deliveryAreas ?? [];
    $selectedDeliveryCode = $selectedDeliveryArea['code'] ?? null;
@endphp
<div class="modal location-modal fade theme-modal" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $locationModalLabelId ?? 'exampleModalLabel' }}">{{ web_t('location.choose_delivery_location', 'Choose your Delivery Location') }}</h5>
                <p class="mt-1 text-content">{{ web_t('location.offer_area_note', 'Enter your address and we will specify the offer for your area.') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="location-list" data-delivery-location>
                    <div class="search-input">
                        <input type="search" class="form-control" placeholder="{{ web_t('location.search_area', 'Search Your Area') }}" data-delivery-search>
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <div class="disabled-box">
                        <h6>{{ web_t('location.select_location', 'Select a Location') }}</h6>
                    </div>

                    <form action="{{ route('store.delivery-location') }}" method="POST" data-delivery-form>
                        @csrf
                        <input type="hidden" name="district_code" value="{{ $selectedDeliveryCode }}">
                    </form>

                    <ul class="location-select custom-height">
                        @foreach ($deliveryAreas as $deliveryArea)
                            <li @class(['active' => $deliveryArea['code'] === $selectedDeliveryCode]) data-delivery-name="{{ mb_strtolower($deliveryArea['name'].' '.$deliveryArea['state']) }}">
                                <a href="javascript:void(0)" data-delivery-code="{{ $deliveryArea['code'] }}">
                                    <h6>{{ $deliveryArea['name'] }}</h6>
                                    @if ($deliveryArea['min_order'] !== null)
                                        <span>{{ web_t('location.min_order', 'Min') }}: ₹{{ number_format($deliveryArea['min_order'], $deliveryArea['min_order'] == floor($deliveryArea['min_order']) ? 0 : 2) }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@once
    <script src="{{ asset('js/delivery-location.js') }}" defer></script>
@endonce
