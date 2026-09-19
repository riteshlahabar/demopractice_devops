@php
    $row = (array) ($row ?? []);
    $index = $index ?? '__INDEX__';
    $isDefault = filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOL);
    $isActive = ! array_key_exists('is_active', $row) || filter_var($row['is_active'], FILTER_VALIDATE_BOOL);
    $variantUnits = $variantUnits ?? [];

    // A row opens on load only when it still needs attention: a brand new row
    // (including the repeater template behind "Add Another Size / Pack") or a
    // row that failed validation. Everything else stays collapsed so a product
    // with many packs keeps the form short.
    $isNewRow = blank($row['id'] ?? null) && blank($row['size_value'] ?? null);
    $hasRowErrors = $index !== '__INDEX__'
        && collect($errors->keys())->contains(fn (string $key): bool => str_starts_with($key, 'variants.'.$index.'.'));
    $isExpanded = $isNewRow || $hasRowErrors;

    $summaryUnit = $variantUnits[$row['unit_id'] ?? ''] ?? '';
    $summarySize = blank($row['size_value'] ?? null)
        ? ''
        : rtrim(rtrim(number_format((float) $row['size_value'], 3, '.', ''), '0'), '.');
    $summaryLabel = trim($summarySize.' '.$summaryUnit);
    $summaryPrice = blank($row['mrp'] ?? null)
        ? ''
        : 'MRP '.rtrim(rtrim(number_format((float) $row['mrp'], 2, '.', ''), '0'), '.');
@endphp
<div class="border rounded mb-3 bg-light admin-variant-row" data-product-variant-row>
    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">

    <div class="d-flex justify-content-between align-items-center gap-2 admin-variant-head">
        <button type="button" class="admin-variant-toggle {{ $isExpanded ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#variantBody{{ $index }}" aria-expanded="{{ $isExpanded ? 'true' : 'false' }}" aria-controls="variantBody{{ $index }}">
            <i class="fa-solid fa-chevron-down admin-variant-caret"></i>
            <span class="admin-variant-summary">
                <span data-variant-summary-label>{{ $summaryLabel !== '' ? $summaryLabel : 'New Variant' }}</span>
                <span class="admin-variant-summary-meta {{ $summaryPrice === '' ? 'd-none' : '' }}" data-variant-summary-price>{{ $summaryPrice }}</span>
                <span class="badge bg-primary-subtle text-primary admin-variant-summary-badge {{ $isDefault ? '' : 'd-none' }}" data-variant-summary-main>Main</span>
                <span class="badge bg-danger-subtle text-danger admin-variant-summary-badge {{ $isActive ? 'd-none' : '' }}" data-variant-summary-inactive>Inactive</span>
            </span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0 me-3" data-remove-repeater-row><i class="fa-solid fa-trash-can"></i> Remove</button>
    </div>

    <div class="collapse {{ $isExpanded ? 'show' : '' }}" id="variantBody{{ $index }}" data-variant-body>
    <div class="row g-3 admin-variant-body">
        <div class="col-md-2">
            <label class="form-label">Size <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0.001" step="0.001" name="variants[{{ $index }}][size_value]" value="{{ $row['size_value'] ?? '' }}" data-variant-size required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Select Unit <span class="text-danger">*</span></label>
            <select class="form-select" name="variants[{{ $index }}][unit_id]" data-variant-unit required>
                <option value="">Select Unit</option>
                @foreach($variantUnits as $unitId => $unitLabel)
                    <option value="{{ $unitId }}" @selected((string) ($row['unit_id'] ?? '') === (string) $unitId)>{{ $unitLabel }}</option>
                @endforeach
            </select>
            @if($variantUnits === [])
                <small class="text-danger">Add units under Products &amp; Inventory &rarr; Unit first.</small>
            @endif
        </div>
        <div class="col-md-2">
            <label class="form-label">Variant SKU</label>
            <input class="form-control" type="text" maxlength="100" name="variants[{{ $index }}][variant_sku]" value="{{ $row['variant_sku'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">HSN Code</label>
            <input class="form-control" type="text" maxlength="40" name="variants[{{ $index }}][hsn_code]" value="{{ $row['hsn_code'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">GST %</label>
            <input class="form-control" type="number" min="0" max="100" step="0.01" name="variants[{{ $index }}][gst_percent]" value="{{ $row['gst_percent'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Units in One Case <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="1" step="1" name="variants[{{ $index }}][units_per_case]" value="{{ $row['units_per_case'] ?? 1 }}" data-units-per-case required>
        </div>

        <div class="col-md-4">
            <label class="form-label">MRP per Retail Pack <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][mrp]" value="{{ $row['mrp'] ?? '' }}" data-variant-mrp required>
            <small class="text-muted">MRP per case: Rs. <span data-case-mrp>0.00</span></small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Dealer Rate per Retail Pack (GST Inclusive) <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][dealer_price]" value="{{ $row['dealer_price'] ?? '' }}" data-variant-dealer-price required>
            <small class="text-muted">Dealer rate per case: Rs. <span data-case-dealer>0.00</span></small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Customer Price per Retail Pack <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][customer_price]" value="{{ $row['customer_price'] ?? '' }}" required>
            <small class="text-muted">Customer quantity 1 = one retail pack.</small>
        </div>

        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" min="0" step="1" name="variants[{{ $index }}][sort_order]" value="{{ $row['sort_order'] ?? 0 }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div>
                <input type="hidden" name="variants[{{ $index }}][is_active]" value="0">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="variants[{{ $index }}][is_active]" value="1" id="variant-active-{{ $index }}" data-variant-active @checked($isActive)>
                    <label class="form-check-label" for="variant-active-{{ $index }}">Active</label>
                </div>
                <input type="hidden" name="variants[{{ $index }}][is_default]" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="variants[{{ $index }}][is_default]" value="1" id="variant-default-{{ $index }}" data-main-display-pack @checked($isDefault)>
                    <label class="form-check-label" for="variant-default-{{ $index }}">Main Product (Main Display Pack)</label>
                </div>
            </div>
        </div>

        <div class="col-12"><hr class="my-0"><small class="text-muted">Optional opening stock for this size. Stock quantity is always entered in retail packs/bottles.</small></div>
        <div class="col-md-3">
            <label class="form-label">Warehouse</label>
            <select class="form-select" name="variants[{{ $index }}][warehouse_id]">
                <option value="">Select Warehouse</option>
                @foreach($variantWarehouses ?? [] as $key => $label)
                    <option value="{{ $key }}" @selected((string) ($row['warehouse_id'] ?? '') === (string) $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Batch No</label>
            <input class="form-control" type="text" maxlength="80" name="variants[{{ $index }}][batch_no]" value="{{ $row['batch_no'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Manufacturing Date</label>
            <input class="form-control" type="date" name="variants[{{ $index }}][manufacturing_date]" value="{{ $row['manufacturing_date'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Expiry Date</label>
            <input class="form-control" type="date" name="variants[{{ $index }}][expiry_date]" value="{{ $row['expiry_date'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Purchase Price per Retail Pack</label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][purchase_price]" value="{{ $row['purchase_price'] ?? 0 }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Opening Stock (Retail Packs)</label>
            <input class="form-control" type="number" min="0" step="0.001" name="variants[{{ $index }}][opening_stock_quantity]" value="{{ $row['opening_stock_quantity'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Low Stock Alert (Retail Packs)</label>
            <input class="form-control" type="number" min="0" step="0.001" name="variants[{{ $index }}][low_stock_alert]" value="{{ $row['low_stock_alert'] ?? 0 }}">
        </div>
    </div>
    </div>
</div>
