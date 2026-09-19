@php
    $infoRows = old('additional_info', $formData['additional_info'] ?? []);
    $infoRows = is_array($infoRows) ? array_values($infoRows) : [];

    if ($infoRows === []) {
        $infoRows = [[]];
    }
@endphp
<label class="form-label">{{ $field['label'] ?? 'Additional Information' }}</label>
<div data-product-additional-info-repeater>
    <div data-repeater-rows>
        @foreach($infoRows as $infoIndex => $infoRow)
            @include('admin.products.partials.additional-information.row', ['row' => $infoRow, 'index' => $infoIndex])
        @endforeach
    </div>
    <template data-repeater-template>
        @include('admin.products.partials.additional-information.row', ['row' => [], 'index' => '__INDEX__'])
    </template>
    <button type="button" class="btn btn-outline-primary btn-sm" data-add-repeater-row><i class="iconoir-plus me-1"></i>Add Another Row</button>
    <small class="text-muted d-block mt-2">Shown on the product detail page as the Additional info table. Left column is the label, right column is the value. Empty rows are ignored.</small>
</div>
