@if(
    $record
    && method_exists($record, 'images')
    && $record->relationLoaded('images')
)
    @php($galleryPreviewImages = $record->images->where('is_primary', false))

    @if($galleryPreviewImages->isNotEmpty())

        <div
            class="admin-gallery-remove-inputs"
            data-gallery-remove-inputs
        ></div>

        <div
            class="admin-gallery-preview-list d-flex flex-wrap gap-2 mt-2"
            data-gallery-removal-list
        >
            @foreach($galleryPreviewImages as $img)

                <div
                    class="admin-gallery-preview-item"
                    data-gallery-preview-item
                >
                    <button
                        type="button"
                        class="admin-gallery-remove-btn"
                        data-gallery-remove-button
                        data-image-id="{{ $img->id }}"
                        data-delete-url="{{ route('admin.products.images.destroy', [$record->getKey(), $img->id]) }}"
                        aria-label="Delete image"
                    >&times;</button>

                    <a href="{{ asset($img->path) }}" target="_blank">
                        <img
                            src="{{ asset($img->path) }}"
                            class="admin-gallery-preview-thumb"
                            alt="Gallery image"
                        >
                    </a>
                </div>

            @endforeach
        </div>

    @endif
@endif