/*
 * Header "Your Location" modal (store/partials/location-modal.blade.php):
 * the search box filters the district list, and clicking a district posts
 * it to store.delivery-location, which remembers it and reloads the page.
 */
(function () {
    'use strict';

    function init(root) {
        var search = root.querySelector('[data-delivery-search]');
        var form = root.querySelector('[data-delivery-form]');
        var items = root.querySelectorAll('[data-delivery-name]');

        if (search) {
            search.addEventListener('input', function () {
                var term = search.value.trim().toLowerCase();

                items.forEach(function (item) {
                    item.style.display = item.getAttribute('data-delivery-name').indexOf(term) === -1 ? 'none' : '';
                });
            });
        }

        if (!form) {
            return;
        }

        root.querySelectorAll('[data-delivery-code]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                form.querySelector('[name="district_code"]').value = link.getAttribute('data-delivery-code');
                form.submit();
            });
        });
    }

    function boot() {
        document.querySelectorAll('[data-delivery-location]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
