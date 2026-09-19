/*
 * State -> District -> Taluka cascading dropdowns, shared by the website
 * sign-up and the admin People forms.
 *
 * Markup: a wrapper with data-location-picker and data-api (base URL of
 * /api/v1/locations) containing
 *   select[data-location="state"], select[data-location="district"],
 *   select[data-location="subdistrict"]  (each may carry data-selected)
 *   [data-location-other] wrapper around the "type taluka" input.
 * The taluka list always ends with "Other" (value "other").
 */
(function () {
    'use strict';

    var OTHER = 'other';

    function fill(select, items, placeholder, selected) {
        select.innerHTML = '';
        var first = document.createElement('option');
        first.value = '';
        first.textContent = placeholder;
        select.appendChild(first);

        items.forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.code;
            option.textContent = item.name;
            select.appendChild(option);
        });

        if (selected !== undefined && selected !== null && selected !== '') {
            select.value = String(selected);
        }

        select.disabled = false;
    }

    function reset(select, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        select.disabled = true;
    }

    function load(url, key) {
        return fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : { data: {} }; })
            .then(function (json) { return (json.data && json.data[key]) || []; })
            .catch(function () { return []; });
    }

    function init(root) {
        var api = (root.getAttribute('data-api') || '').replace(/\/$/, '');
        var state = root.querySelector('[data-location="state"]');
        var district = root.querySelector('[data-location="district"]');
        var subdistrict = root.querySelector('[data-location="subdistrict"]');
        var otherWrap = root.querySelector('[data-location-other]');
        // State + District only pickers (admin Delivery Areas) have no taluka select.
        var hasSubdistrict = !!subdistrict;

        if (!api || !state || !district) {
            return;
        }

        subdistrict = subdistrict || document.createElement('select');

        function syncOther() {
            if (otherWrap) {
                otherWrap.style.display = subdistrict.value === OTHER ? '' : 'none';
            }
        }

        function loadSubdistricts(selected) {
            if (!district.value) {
                reset(subdistrict, 'Select taluka');
                syncOther();
                return Promise.resolve();
            }

            subdistrict.disabled = true;

            return load(api + '/subdistricts?district=' + encodeURIComponent(district.value), 'subdistricts').then(function (items) {
                fill(subdistrict, items.concat([{ code: OTHER, name: 'Other (type taluka)' }]), 'Select taluka', selected);
                syncOther();
            });
        }

        function loadDistricts(selected, selectedSubdistrict) {
            reset(subdistrict, 'Select taluka');
            syncOther();

            if (!state.value) {
                reset(district, 'Select district');
                return Promise.resolve();
            }

            district.disabled = true;

            return load(api + '/districts?state=' + encodeURIComponent(state.value), 'districts').then(function (items) {
                fill(district, items, 'Select district', selected);

                return hasSubdistrict && selectedSubdistrict !== undefined ? loadSubdistricts(selectedSubdistrict) : null;
            });
        }

        state.addEventListener('change', function () { loadDistricts(); });
        district.addEventListener('change', function () {
            if (hasSubdistrict) {
                loadSubdistricts();
            }
        });
        subdistrict.addEventListener('change', syncOther);

        reset(district, 'Select district');
        reset(subdistrict, 'Select taluka');
        syncOther();

        load(api + '/states', 'states').then(function (items) {
            fill(state, items, 'Select state', state.getAttribute('data-selected'));

            if (state.value) {
                loadDistricts(district.getAttribute('data-selected'), subdistrict.getAttribute('data-selected'));
            }
        });
    }

    function boot() {
        document.querySelectorAll('[data-location-picker]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
