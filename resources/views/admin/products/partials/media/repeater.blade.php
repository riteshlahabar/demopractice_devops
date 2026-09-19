@php
    $mediaRows = old('media', $formData['media'] ?? []);
    $mediaRows = is_array($mediaRows) ? $mediaRows : [];
@endphp
<div data-product-media-repeater>
    <div data-repeater-rows>
        @foreach($mediaRows as $mediaIndex => $mediaRow)
            @include('admin.products.partials.media.row', ['row' => $mediaRow, 'index' => $mediaIndex])
        @endforeach
    </div>
    <template data-repeater-template>
        @include('admin.products.partials.media.row', ['row' => [], 'index' => '__INDEX__'])
    </template>
    <button type="button" class="btn btn-outline-primary" data-add-repeater-row><i class="iconoir-plus me-1"></i>Add Another Video</button>
</div>
