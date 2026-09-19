@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    // $moduleAccess comes from AdminAccessServiceProvider (the signed-in role).
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $submenuTitle = request()->query('row_title');
    $submenuSingular = $module['singular'];

    if (is_string($submenuTitle) && $submenuTitle !== '') {
        $submenuSingular = preg_replace('/^Row\s+\d+\s*-\s*/i', '', $submenuTitle);
    }

    $perPageOptions = [10, 25, 50, 100, 500];
    $currentPerPage = (int) request()->integer('per_page', 10);
    if (! in_array($currentPerPage, $perPageOptions, true)) {
        $currentPerPage = 10;
    }
@endphp
<div class="card admin-table-card" data-table-key="{{ $module['key'] }}">
    <div class="card-body pt-3">
        <div class="d-flex justify-content-end gap-2 mb-3">
            @if($module['key'] === 'salary' && $can['edit'])
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#salaryModal"><i class="iconoir-dollar-circle me-1"></i>Generate Salary</button>
            @endif
            @if($module['key'] === 'translations' && $can['edit'])
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#appTranslateModal"><i class="iconoir-translate me-1"></i>Translate</button>
            @endif
            @if($module['key'] === 'backups' && $can['create'])
                <form method="POST" action="{{ route('admin.backups.run') }}">@csrf<button class="btn btn-success"><i class="iconoir-database-backup me-1"></i>Take Backup Now</button></form>
            @endif
            @if(($module['can_create'] ?? true) && $can['create'])
                <a href="{{ route($module['route'].'.create', request()->only(['type','placement','section_key','row_title'])) }}" class="btn btn-primary"><i class="iconoir-plus-circle me-1"></i>Add {{ $submenuSingular }}</a>
            @endif
        </div>
        @include('admin.shared.table-toolbar')


        <form id="bulkActionForm" method="POST" action="{{ route($module['route'].'.bulk-destroy') }}">
            @csrf
            @method('DELETE')
            @foreach(request()->query() as $key => $value)
                @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach

            <div class="table-responsive admin-table-responsive">
                <table class="table table-hover align-middle mb-0 admin-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="bulk-select-col">@if($can['delete'])<input class="form-check-input admin-select-all" type="checkbox" title="Select all">@endif</th>
                            @foreach($module['columns'] as $index => $column)
                                <th data-column-index="{{ $index }}">{{ $column['label'] }}</th>
                            @endforeach
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="bulk-select-col">@if($can['delete'])<input class="form-check-input admin-row-checkbox" type="checkbox" name="selected_ids[]" value="{{ $record->getKey() }}">@endif</td>
                                @foreach($module['columns'] as $index => $column)
                                    @php
                                        $value = data_get($record, $column['key']);
                                        if (($column['type'] ?? '') === 'image' && empty($value)) {
                                            foreach (($column['fallback_keys'] ?? []) as $fallbackKey) {
                                                $value = data_get($record, $fallbackKey);
                                                if (! empty($value)) break;
                                            }
                                        }
                                        $imageUrl = null;
                                        if (($column['type'] ?? '') === 'image' && ! empty($value)) {
                                            $imageUrl = str_starts_with((string) $value, 'http://') || str_starts_with((string) $value, 'https://') || str_starts_with((string) $value, '/')
                                                ? url((string) $value)
                                                : asset((string) $value);
                                        }
                                    @endphp
                                    <td data-column-index="{{ $index }}">
                                        @if(($column['type'] ?? '') === 'image')
                                            @if($imageUrl)
                                                @php($imageModalId = 'imagePreview'.$module['key'].$record->getKey().$index)
                                                <button type="button" class="admin-image-thumb-btn" data-bs-toggle="modal" data-bs-target="#{{ $imageModalId }}" title="Preview image">
                                                    <img src="{{ $imageUrl }}" class="admin-image-thumb" alt="{{ $column['label'] }}">
                                                </button>
                                                <div class="modal fade admin-image-preview-modal" id="{{ $imageModalId }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $column['label'] }} Preview</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ $imageUrl }}" class="admin-image-preview" alt="{{ $column['label'] }} preview">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @elseif(($column['type'] ?? '') === 'boolean')
                                            <span class="badge {{ $value ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $value ? 'Active' : 'Inactive' }}</span>
                                        @elseif(($column['type'] ?? '') === 'status')
                                            <span class="badge bg-{{ in_array($value, ['active','approved','paid','delivered','verified','collected']) ? 'success' : (in_array($value, ['rejected','cancelled','inactive']) ? 'danger' : 'warning') }}-subtle text-{{ in_array($value, ['active','approved','paid','delivered','verified','collected']) ? 'success' : (in_array($value, ['rejected','cancelled','inactive']) ? 'danger' : 'warning') }}">{{ str($value)->replace('_', ' ')->title() }}</span>
                                        @elseif(($column['type'] ?? '') === 'money')
                                            Rs. {{ number_format((float) $value, 2) }}
                                        @elseif(($column['type'] ?? '') === 'email')
                                            @php($email = \App\Models\User::displayEmail($value))
                                            @if($email)<a href="mailto:{{ $email }}" class="text-title">{{ $email }}</a>@else<span class="text-muted">-</span>@endif
                                        @elseif(($column['type'] ?? '') === 'date')
                                            {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y') : '-' }}
                                        @elseif(($column['type'] ?? '') === 'datetime')
                                            {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y h:i A') : '-' }}
                                        @else
                                            {{ $value !== null && $value !== '' ? $value : '-' }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <div class="dropdown admin-row-action">
                                        <button class="btn btn-sm btn-outline-secondary admin-row-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" aria-label="Actions">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end admin-row-action-menu">
                                            <a class="dropdown-item" href="{{ route($module['route'].'.show', array_merge([$record->getKey()], request()->only(['type','placement','section_key','row_title']))) }}"><i class="iconoir-eye"></i><span>View</span></a>
                                            @if(($module['can_edit'] ?? true) && $can['edit'])
                                                <a class="dropdown-item" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], request()->only(['type','placement','section_key','row_title']))) }}"><i class="iconoir-edit-pencil"></i><span>Edit</span></a>
                                            @endif

                                            @if($module['key'] === 'products')
                                                <div class="dropdown-divider"></div>
                                                @foreach(['is_top_selling' => 'Top Selling', 'is_deal_timer_product' => 'Timer Deal', 'is_active' => 'Status'] as $flagKey => $flagLabel)
                                                    @php($flagValue = (bool) data_get($record, $flagKey))
                                                    <span class="dropdown-item-text admin-row-action-flag"><span>{{ $flagLabel }}</span><span class="badge {{ $flagValue ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $flagValue ? 'Active' : 'Inactive' }}</span></span>
                                                @endforeach
                                            @endif

                                            @if($module['key'] === 'dealers' && $record->status === 'pending_approval' && $can['edit'])
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="button" data-bs-toggle="modal" data-bs-target="#approveDealer{{ $record->id }}"><i class="iconoir-check-circle"></i><span>Approve Dealer</span></button>
                                            @endif

                                            @if($module['key'] === 'orders')
                                                <div class="dropdown-divider"></div>
                                                @if($can['edit'])
                                                @if(! in_array((string) $record->status, ['cancelled', 'delivered'], true))
                                                <button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#orderCancel{{ $record->id }}"><i class="iconoir-cancel"></i><span>Cancel Order</span></button>
                                                @endif
                                                <button class="dropdown-item text-info" type="submit" form="convertOrderToPi{{ $record->id }}"><i class="iconoir-page"></i><span>Convert to PI</span></button>
                                                @endif
                                                <a class="dropdown-item" href="{{ route('admin.sales-documents.print', ['document' => 'order', 'id' => $record->getKey()]) }}" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.sales-documents.pdf', ['document' => 'order', 'id' => $record->getKey()]) }}"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            @endif

                                            @if($module['key'] === 'proforma-invoices')
                                                <div class="dropdown-divider"></div>
                                                @if($can['edit'])<button class="dropdown-item text-info" type="submit" form="convertPiToInvoice{{ $record->id }}"><i class="iconoir-receipt"></i><span>Convert to Sale Invoice</span></button>@endif
                                                <a class="dropdown-item" href="{{ route('admin.sales-documents.print', ['document' => 'proforma', 'id' => $record->getKey()]) }}" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.sales-documents.pdf', ['document' => 'proforma', 'id' => $record->getKey()]) }}"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            @endif

                                            @if($module['key'] === 'invoices')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('admin.sales-documents.print', ['document' => 'invoice', 'id' => $record->getKey()]) }}" target="_blank"><i class="fa-solid fa-print"></i><span>Print A4</span></a>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.sales-documents.pdf', ['document' => 'invoice', 'id' => $record->getKey()]) }}"><i class="fa-solid fa-file-pdf"></i><span>Download PDF</span></a>
                                            @endif

                                            @if($module['key'] === 'resignations' && $can['edit'])
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-info" type="submit" form="suggestSettlement{{ $record->id }}"><i class="iconoir-calculator"></i><span>Fill F&amp;F From Records</span></button>
                                            @endif

                                            @if($module['key'] === 'backups' && $record->status === 'completed')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('admin.backups.download', $record->getKey()) }}"><i class="iconoir-download"></i><span>Download</span></a>
                                                @if($can['delete'])
                                                    <button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#restoreBackup{{ $record->id }}"><i class="iconoir-undo-action"></i><span>Restore Database</span></button>
                                                @endif
                                            @endif

                                            @if(in_array($module['key'], ['expenses','leaves']) && $record->status === 'pending' && $can['edit'])
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-success" type="submit" form="decisionForm{{ $module['key'] }}{{ $record->id }}" name="status" value="approved"><i class="iconoir-check-circle"></i><span>Approve</span></button>
                                                <button class="dropdown-item text-danger" type="submit" form="decisionForm{{ $module['key'] }}{{ $record->id }}" name="status" value="rejected"><i class="iconoir-xmark-circle"></i><span>Reject</span></button>
                                            @endif

                                            @if(($module['can_delete'] ?? true) && $can['delete'])
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-danger" type="button" onclick="if(confirm('Delete this record?')) document.getElementById('deleteForm{{ $module['key'] }}{{ $record->id }}').submit();"><i class="fa-solid fa-trash-can"></i><span>Delete</span></button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($module['columns']) + 2 }}" class="text-center py-5 text-muted">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @foreach($records as $record)
            @if($module['can_delete'] ?? true)
                <form id="deleteForm{{ $module['key'] }}{{ $record->id }}" method="POST" action="{{ route($module['route'].'.destroy', array_merge([$record->getKey()], request()->only(['type','placement','section_key','row_title']))) }}" class="d-none">@csrf @method('DELETE')</form>
            @endif
            @if(in_array($module['key'], ['expenses','leaves']) && $record->status === 'pending')
                <form id="decisionForm{{ $module['key'] }}{{ $record->id }}" method="POST" action="{{ route('admin.'.$module['key'].'.decision', $record->id) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'orders')
                <form id="convertOrderToPi{{ $record->id }}" method="POST" action="{{ route('admin.orders.convert-to-proforma', $record->getKey()) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'proforma-invoices')
                <form id="convertPiToInvoice{{ $record->id }}" method="POST" action="{{ route('admin.proforma-invoices.convert-to-invoice', $record->getKey()) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'dealers' && $record->status === 'pending_approval')
                <div class="modal fade" id="approveDealer{{ $record->id }}"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.dealers.approve', $record->id) }}">@csrf<div class="modal-header"><h5>Approve {{ $record->name }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Assign Salesman</label><select name="salesman_id" class="form-select" required>@foreach(\App\Models\User::where('role', 'salesman')->where('status', 'active')->orderBy('name')->get() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select><label class="form-label mt-3">Credit Limit</label><input name="credit_limit" type="number" step="0.01" min="0" class="form-control" value="0"></div><div class="modal-footer"><button class="btn btn-success">Approve & Assign</button></div></form></div></div>
            @endif
            @if($module['key'] === 'resignations')
                <form id="suggestSettlement{{ $record->id }}" method="POST" action="{{ route('admin.resignations.suggest-settlement', $record->getKey()) }}" class="d-none">@csrf</form>
            @endif
            @if($module['key'] === 'backups' && $record->status === 'completed')
                <div class="modal fade" id="restoreBackup{{ $record->id }}"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.backups.restore', $record->getKey()) }}">@csrf<div class="modal-header"><h5>Restore from {{ $record->filename }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-danger mb-3"><strong>This overwrites the live database.</strong> Every table in this backup is dropped and recreated, and anything entered since {{ $record->created_at?->format('d M Y H:i') }} is lost. Take a fresh backup first if you are not certain.</div><label class="form-label">Type <code>RESTORE</code> to confirm</label><input class="form-control" name="confirm" autocomplete="off" placeholder="RESTORE" required></div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Restore Database</button></div></form></div></div>
            @endif
            @if($module['key'] === 'orders')
                <div class="modal fade" id="orderCancel{{ $record->id }}"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.orders.cancel', $record->id) }}">@csrf<div class="modal-header"><h5>Cancel {{ $record->order_no }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Reason for cancellation <span class="text-danger">*</span></label><textarea name="cancel_reason" class="form-control" rows="3" maxlength="500" required placeholder="Why is this order being cancelled?"></textarea><small class="text-muted">Every other status is set automatically by the invoice and dispatch steps.</small></div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-danger">Cancel Order</button></div></form></div></div>
            @endif
        @endforeach

        <div class="admin-table-pagination mt-3">
            <div class="admin-table-result-meta">
                Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} results
            </div>
            <form method="GET" class="admin-table-per-page-form">
                @foreach(request()->except(['page', 'per_page']) as $key => $value)
                    @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                @endforeach
                <label class="form-label mb-0" for="adminPerPage{{ $module['key'] }}">Show</label>
                <select id="adminPerPage{{ $module['key'] }}" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($currentPerPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <span>rows</span>
            </form>
            <div class="admin-table-links">
                {{ $records->onEachSide(1)->links('pagination::admin') }}
            </div>
        </div>
    </div>
</div>

@if($module['key'] === 'translations')
    @include('admin.translations.translate-modal')
@endif
@if($module['key'] === 'salary')
    <div class="modal fade" id="salaryModal"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.salary.generate') }}">@csrf<div class="modal-header"><h5>Generate Monthly Salary</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row"><div class="col-6"><label>Year</label><input class="form-control" type="number" name="salary_year" value="{{ now()->year }}" required></div><div class="col-6"><label>Month</label><select class="form-select" name="salary_month">@foreach(range(1, 12) as $m)<option value="{{ $m }}" @selected($m === now()->month)>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>@endforeach</select></div></div></div><div class="modal-footer"><button class="btn btn-success">Generate</button></div></form></div></div>
@endif
@endsection
