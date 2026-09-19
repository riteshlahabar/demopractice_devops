@php
    $row = (array) ($row ?? []);
    $index = $index ?? '__INDEX__';
@endphp
<div class="row g-2 align-items-end mb-2" data-product-additional-info-row>
    <div class="col-md-5">
        <input class="form-control" type="text" maxlength="120" name="additional_info[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="Label - example: Net Quantity">
    </div>
    <div class="col-md-6">
        <input class="form-control" type="text" maxlength="255" name="additional_info[{{ $index }}][value]" value="{{ $row['value'] ?? '' }}" placeholder="Value - example: 500 ML">
    </div>
    <div class="col-md-1">
        <button type="button" class="btn btn-outline-danger w-100" data-remove-repeater-row title="Remove row"><i class="iconoir-trash"></i></button>
    </div>
</div>
