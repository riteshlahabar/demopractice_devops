{{-- State / District / Taluka dropdowns (filled by public/js/location-picker.js) plus City and Pincode, in the Fastkart form-floating style. --}}
<div class="col-12" data-location-picker data-api="{{ url('api/v1/locations') }}">
    <div class="row g-4">
        <div class="col-12">
            <div class="form-floating theme-form-floating">
                <select class="form-select @error('state_code') is-invalid @enderror" id="state_code" name="state_code" data-location="state" data-selected="{{ old('state_code') }}">
                    <option value="">Select state</option>
                </select>
                <label for="state_code">State</label>
                @error('state_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating theme-form-floating">
                <select class="form-select @error('district_code') is-invalid @enderror" id="district_code" name="district_code" data-location="district" data-selected="{{ old('district_code') }}" disabled>
                    <option value="">Select district</option>
                </select>
                <label for="district_code">District</label>
                @error('district_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating theme-form-floating">
                <select class="form-select @error('subdistrict_code') is-invalid @enderror" id="subdistrict_code" name="subdistrict_code" data-location="subdistrict" data-selected="{{ old('subdistrict_code') }}" disabled>
                    <option value="">Select taluka</option>
                </select>
                <label for="subdistrict_code">Sub-district / Taluka</label>
                @error('subdistrict_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12" data-location-other style="display: none">
            <div class="form-floating theme-form-floating">
                <input type="text" class="form-control @error('subdistrict_name') is-invalid @enderror" id="subdistrict_name" name="subdistrict_name" value="{{ old('subdistrict_name') }}" placeholder="Taluka Name">
                <label for="subdistrict_name">Taluka Name</label>
                @error('subdistrict_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating theme-form-floating">
                <input type="text" class="form-control @error('city_village') is-invalid @enderror" id="city_village" name="city_village" value="{{ old('city_village') }}" placeholder="City / Village">
                <label for="city_village">City / Village</label>
                @error('city_village')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating theme-form-floating">
                <input type="text" class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" value="{{ old('pincode') }}" placeholder="Pincode" inputmode="numeric" maxlength="6">
                <label for="pincode">Pincode</label>
                @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
