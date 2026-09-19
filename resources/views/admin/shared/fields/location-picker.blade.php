{{-- State / District / Taluka cascade (public/js/location-picker.js) with City and Pincode. Values come from PeopleModuleController::formData(). --}}
@php
    $locationValue = fn (string $name) => old($name, $formData[$name] ?? null);
    $locationRequired = (bool) ($module['location_required'] ?? true);
    $requiredMark = $locationRequired ? ' <span class="text-danger" title="Required field">*</span>' : '';
@endphp
<div class="col-12">
    <h6 class="mt-2 mb-0">Location</h6>
</div>
<div class="col-12" data-location-picker data-api="{{ url('api/v1/locations') }}">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="state_code">State{!! $requiredMark !!}</label>
            <select class="form-select @error('state_code') is-invalid @enderror" id="state_code" name="state_code" data-location="state" data-selected="{{ $locationValue('state_code') }}">
                <option value="">Select state</option>
            </select>
            @error('state_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="district_code">District{!! $requiredMark !!}</label>
            <select class="form-select @error('district_code') is-invalid @enderror" id="district_code" name="district_code" data-location="district" data-selected="{{ $locationValue('district_code') }}" disabled>
                <option value="">Select district</option>
            </select>
            @error('district_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="subdistrict_code">Sub-district / Taluka{!! $requiredMark !!}</label>
            <select class="form-select @error('subdistrict_code') is-invalid @enderror" id="subdistrict_code" name="subdistrict_code" data-location="subdistrict" data-selected="{{ $locationValue('subdistrict_code') }}" disabled>
                <option value="">Select taluka</option>
            </select>
            @error('subdistrict_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4" data-location-other style="display: none">
            <label class="form-label" for="subdistrict_name">Taluka Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('subdistrict_name') is-invalid @enderror" id="subdistrict_name" name="subdistrict_name" value="{{ $locationValue('subdistrict_name') }}">
            @error('subdistrict_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="city_village">City / Village{!! $requiredMark !!}</label>
            <input type="text" class="form-control @error('city_village') is-invalid @enderror" id="city_village" name="city_village" value="{{ $locationValue('city_village') }}">
            @error('city_village')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="pincode">Pincode{!! $requiredMark !!}</label>
            <input type="text" class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" value="{{ $locationValue('pincode') }}" inputmode="numeric" maxlength="6">
            @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
@once
    <script src="{{ asset('js/location-picker.js') }}" defer></script>
@endonce
