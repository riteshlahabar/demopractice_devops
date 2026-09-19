{{-- State -> District cascade (public/js/location-picker.js, no taluka). Values come from DeliveryAreaController::formData(). --}}
@php
    $districtValue = fn (string $name) => old($name, $formData[$name] ?? null);
@endphp
<div class="col-12" data-location-picker data-api="{{ url('api/v1/locations') }}">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="state_code">State <span class="text-danger" title="Required field">*</span></label>
            <select class="form-select @error('state_code') is-invalid @enderror" id="state_code" name="state_code" data-location="state" data-selected="{{ $districtValue('state_code') ?: config('storefront.default_delivery_state_code') }}">
                <option value="">Select state</option>
            </select>
            @error('state_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="district_code">District <span class="text-danger" title="Required field">*</span></label>
            <select class="form-select @error('district_code') is-invalid @enderror" id="district_code" name="district_code" data-location="district" data-selected="{{ $districtValue('district_code') }}" disabled>
                <option value="">Select district</option>
            </select>
            @error('district_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
@once
    <script src="{{ asset('js/location-picker.js') }}" defer></script>
@endonce
