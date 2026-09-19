<div class="col-12">
    <div class="d-flex flex-wrap align-items-center gap-2 border rounded px-3 py-2 bg-light">
        <button
            type="button"
            class="btn btn-sm btn-primary"
            data-product-auto-translate
            data-url="{{ route('admin.products.translate') }}"
        >
            Auto Translate
        </button>

        <small class="text-muted">
            Uses Product Name and Description. You can edit every language before saving.
        </small>

        <span
            class="small text-muted d-none"
            data-product-auto-translate-status
        ></span>
    </div>
</div>