@php
    $variantRows = old('variants', $formData['variants'] ?? []);
    $variantRows = is_array($variantRows) ? $variantRows : [];
    $variantWarehouses = $options['variant_warehouses'] ?? [];
    $variantUnits = $options['variant_units'] ?? [];

    // At least one variant is mandatory, so a new product always starts with an
    // empty row that is pre-marked as the Main Product.
    if ($variantRows === []) {
        $variantRows = [['is_active' => true, 'is_default' => true]];
    }
@endphp
<div data-product-variants-repeater>
    <div data-repeater-rows>
        @foreach($variantRows as $variantIndex => $variantRow)
            @include('admin.products.partials.variants.row', ['row' => $variantRow, 'index' => $variantIndex, 'variantWarehouses' => $variantWarehouses, 'variantUnits' => $variantUnits])
        @endforeach
    </div>
    <template data-repeater-template>
        @include('admin.products.partials.variants.row', ['row' => [], 'index' => '__INDEX__', 'variantWarehouses' => $variantWarehouses, 'variantUnits' => $variantUnits])
    </template>
    <button type="button" class="btn btn-outline-primary" data-add-repeater-row><i class="iconoir-plus me-1"></i>Add Another Size / Pack</button>
    <small class="text-muted d-block mt-2">Main Product is used by direct Add to Cart. Dealer: quantity 1 = one case. Customer: quantity 1 = one retail pack.</small>
</div>
