(function () {
    'use strict';

function initGalleryImageRemoval() {
        document.querySelectorAll('[data-gallery-removal-list]').forEach(function (list) {
            var form = list.closest('form');
            var inputsHost = form ? form.querySelector('[data-gallery-remove-inputs]') : null;

            if (!form) {
                return;
            }

            list.addEventListener('click', function (event) {
                var button = event.target.closest('[data-gallery-remove-button]');
                if (!button) {
                    return;
                }

                event.preventDefault();

                var item = button.closest('[data-gallery-preview-item]');
                var imageId = button.dataset.imageId || '';
                var deleteUrl = button.dataset.deleteUrl || '';
                var token = form.querySelector('input[name="_token"]');

                if (!item) {
                    return;
                }

                if (deleteUrl) {
                    if (!window.confirm('Delete this image permanently?')) {
                        return;
                    }

                    button.disabled = true;

                    var formData = new FormData();
                    formData.append('_method', 'DELETE');
                    if (button.dataset.imageField) {
                        formData.append('field', button.dataset.imageField);
                    }

                    fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token ? token.value : ''
                        },
                        body: formData
                    })
                        .then(function (response) {
                            return response.json().catch(function () {
                                return {};
                            }).then(function (payload) {
                                if (!response.ok) {
                                    throw payload;
                                }
                                return payload;
                            });
                        })
                        .then(function () {
                            item.remove();
                        })
                        .catch(function (error) {
                            button.disabled = false;
                            alert(error && error.message ? error.message : 'Image could not be deleted.');
                        });
                    return;
                }

                if (!inputsHost || !imageId) {
                    return;
                }

                var existingInput = Array.from(inputsHost.querySelectorAll('input[name="remove_gallery_image_ids[]"]')).find(function (input) {
                    return input.value === imageId;
                });

                if (!existingInput) {
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'remove_gallery_image_ids[]';
                    hiddenInput.value = imageId;
                    inputsHost.appendChild(hiddenInput);
                }

                item.remove();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleryImageRemoval);
    } else {
        initGalleryImageRemoval();
    }
})();
