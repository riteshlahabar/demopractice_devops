@php
    $deleteUrl = null;
    $deleteField = null;

    if ($record) {

        if (
            $name === 'primary_image'
            && ! empty($formData['primary_image_id'])
        ) {
            $deleteUrl = route(
                'admin.products.images.destroy',
                [
                    $record->getKey(),
                    $formData['primary_image_id'],
                ]
            );
        }
        elseif ($name !== 'primary_image') {
            $deleteUrl = route(
                'admin.products.field-image.destroy',
                $record->getKey()
            );

            $deleteField = $name;
        }
    }
@endphp

<div
    class="admin-gallery-preview-list d-flex flex-wrap gap-2 mt-2"
    data-gallery-removal-list
>
    <div
        class="admin-gallery-preview-item"
        data-gallery-preview-item
    >
        @if($deleteUrl)

            <button
                type="button"
                class="admin-gallery-remove-btn"
                data-gallery-remove-button
                data-delete-url="{{ $deleteUrl }}"
                @if($deleteField)
                    data-image-field="{{ $deleteField }}"
                @endif
                aria-label="Delete image"
            >&times;</button>

        @endif

        <a href="{{ asset($value) }}" target="_blank">
            <img
                src="{{ asset($value) }}"
                class="admin-gallery-preview-thumb"
                alt="Current image"
            >
        </a>
    </div>
</div>