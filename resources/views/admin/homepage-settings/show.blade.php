@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">{{ $record->title }}</h5>
                <p class="mb-1"><strong>Type:</strong> {{ str($record->section_type)->replace('_', ' ')->title() }}</p>
                <p class="mb-1"><strong>Layout:</strong> {{ $record->layout_type ?: '-' }}</p>
<p class="mb-1"><strong>Category:</strong> {{ $record->category?->name ?: '-' }}</p>
                <p class="mb-1"><strong>Product Limit:</strong> {{ $record->product_limit }}</p>
                <p class="mb-1"><strong>Item Limit:</strong> {{ $record->item_limit ?: '-' }}</p>
                <p class="mb-1"><strong>Image Note:</strong> {{ $record->image_size_note ?: '-' }}</p>
                <p class="mb-1"><strong>Sort Order:</strong> {{ $record->sort_order }}</p>
                <p class="mb-0"><strong>Status:</strong> {{ $record->is_active ? 'Active' : 'Inactive' }}</p>

                <div class="d-flex gap-2 mt-4">
                    <a class="btn btn-primary" href="{{ route('admin.homepage-settings.edit', $record->id) }}">Edit Section</a>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.homepage-settings.index') }}">Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Section Content</h5>
                        <small class="text-muted">Products are assigned from Product Add/Edit using Homepage Section Title. This page only defines homepage row/design.</small>
                    </div>
                    <a class="btn btn-success" href="{{ route('admin.homepage-setting-items.create', ['section_id' => $record->id, 'row_title' => $record->title.' Content']) }}">
                        Add Content
                    </a>
                </div>

                @if(in_array($record->section_type, ['product_section', 'category_section', 'top_selling_section'], true))
                    <div class="alert alert-info">
                        <strong>No duplicate product adding required.</strong>
                        @if($record->section_type === 'product_section')
                            This row shows active products from selected category/source.
                        @elseif($record->section_type === 'top_selling_section')
                            This row uses products where <strong>Top Selling Product</strong> is enabled. Timer card uses product where <strong>Deal Timer Product</strong> is enabled.
                        @else
                            This row uses active categories.
                        @endif
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Image/Icon</th>
                                <th>Title</th>
                                <th>Coupon</th>
                                <th>Sort</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($record->items as $item)
                                <tr>
                                    <td>
                                        @php($preview = $item->image_path ?: $item->logo_image_path)
                                        @if($preview)
                                            <img src="{{ asset($preview) }}" style="width:70px;height:45px;object-fit:cover;border-radius:6px;border:1px solid #ddd;" alt="">
                                        @else
                                            {{ $item->icon_key ?: '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $item->title ?: $item->highlight_text ?: '-' }}</td>
                                    <td>{{ $item->coupon_code ?: '-' }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.homepage-setting-items.edit', $item->id) }}">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No content added yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection