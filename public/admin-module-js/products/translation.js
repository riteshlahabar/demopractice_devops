(function () {
    'use strict';

function initProductAutoTranslate() {
        document.querySelectorAll('[data-product-auto-translate]').forEach(function (button) {
            button.addEventListener('click', function () {
                var form = button.closest('form');
                if (!form) {
                    return;
                }

                var status = form.querySelector('[data-product-auto-translate-status]');
                var token = form.querySelector('input[name="_token"]');
                var nameInput = form.elements.namedItem('name');
                var descriptionInput = form.elements.namedItem('description');
                var name = nameInput ? nameInput.value.trim() : '';
                var description = descriptionInput ? descriptionInput.value.trim() : '';

                if (!name) {
                    if (status) {
                        status.classList.remove('d-none', 'text-success');
                        status.classList.add('text-danger');
                        status.textContent = 'Enter Product Name first.';
                    }
                    return;
                }

                button.disabled = true;
                if (status) {
                    status.classList.remove('d-none', 'text-danger', 'text-success');
                    status.textContent = 'Translating...';
                }

                fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token ? token.value : ''
                    },
                    body: JSON.stringify({ name: name, description: description })
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok) {
                                throw payload;
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        var translations = payload.translations || {};
                        ['hi', 'mr', 'gu', 'kn', 'te'].forEach(function (locale) {
                            var localeTranslation = translations[locale] || {};
                            var nameField = form.elements.namedItem('translation_' + locale + '_name');
                            var descriptionField = form.elements.namedItem('translation_' + locale + '_description');

                            if (nameField && localeTranslation.name) {
                                nameField.value = localeTranslation.name;
                            }

                            if (descriptionField && localeTranslation.description) {
                                descriptionField.value = localeTranslation.description;
                            }
                        });

                        if (status) {
                            status.classList.remove('d-none', 'text-danger');
                            status.classList.add('text-success');
                            status.textContent = 'Translation filled. Review and save product.';
                        }
                    })
                    .catch(function (error) {
                        var message = error && (error.message || error.error) ? (error.message || error.error) : 'Auto translation failed. Enter translations manually.';
                        if (status) {
                            status.classList.remove('d-none', 'text-success');
                            status.classList.add('text-danger');
                            status.textContent = message;
                        }
                    })
                    .finally(function () {
                        button.disabled = false;
                    });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductAutoTranslate);
    } else {
        initProductAutoTranslate();
    }
})();
