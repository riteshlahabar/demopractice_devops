@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $isSuper = (bool) ($record?->is_super);
@endphp
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                <form method="POST" action="{{ $record ? route('admin.admin-roles.update', $record) : route('admin.admin-roles.store') }}" class="theme-form theme-form-2 mega-form" data-role-permissions>
                    @csrf
                    @if($record) @method('PUT') @endif

                    <div class="card-header-2"><h5>{{ $pageTitle }}</h5></div>

                    <div class="mb-4 row align-items-center">
                        <label class="form-label-title col-sm-2 mb-0" for="roleName">Name <span class="theme-color">*</span></label>
                        <div class="col-sm-10">
                            <input id="roleName" class="form-control" type="text" name="name" value="{{ old('name', $record?->name) }}" maxlength="100" required>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label class="form-label-title col-sm-2 mb-0" for="roleDescription">Description</label>
                        <div class="col-sm-10">
                            <input id="roleDescription" class="form-control" type="text" name="description" value="{{ old('description', $record?->description) }}" maxlength="255">
                        </div>
                    </div>

                    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h4 class="form-label-title mb-0">Permissions</h4>
                        @unless($isSuper)
                            <div class="d-flex align-items-center gap-2">
                                <input class="checkbox_animated" type="checkbox" id="permAll" data-check-scope="all">
                                <label class="form-check-label m-0" for="permAll">Select everything</label>
                            </div>
                        @endunless
                    </div>

                    @if($isSuper)
                        <div class="alert alert-info mb-0">Super Admin has full access to every section. Its permissions cannot be changed.</div>
                    @else
                        <p class="text-muted small">Add, Edit or Delete also gives View, because those actions start from the list page.</p>

                        @foreach($groups as $groupIndex => $group)
                            <div class="card border mb-3" data-perm-group="{{ $groupIndex }}">
                                <div class="card-body py-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <h5 class="mb-0">{{ $group['label'] }}</h5>
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="checkbox_animated" type="checkbox" id="permGroup{{ $groupIndex }}" data-check-scope="group">
                                            <label class="form-check-label m-0" for="permGroup{{ $groupIndex }}">All in {{ $group['label'] }}</label>
                                        </div>
                                    </div>
                                    <div class="row roles-form">
                                        @foreach($group['sections'] as $section)
                                            @php($rowId = 'perm-'.$section['key'])
                                            <div class="col-12" data-perm-row>
                                                <ul>
                                                    <li><strong>{{ $section['label'] }} :</strong></li>
                                                    <li>
                                                        <input class="checkbox_animated" type="checkbox" id="{{ $rowId }}-all" data-check-scope="row">
                                                        <label class="form-check-label m-0" for="{{ $rowId }}-all">All</label>
                                                    </li>
                                                    @foreach($actionLabels as $action => $label)
                                                        <li>
                                                            @if(in_array($action, $section['actions'], true))
                                                                <input class="checkbox_animated" type="checkbox" id="{{ $rowId }}-{{ $action }}" name="permissions[{{ $section['key'] }}][{{ $action }}]" value="1" data-action="{{ $action }}" @checked(in_array($action, $granted[$section['key']] ?? [], true))>
                                                                <label class="form-check-label m-0" for="{{ $rowId }}-{{ $action }}">{{ $label }}</label>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4 admin-form-actions">
                        <a class="btn btn-outline-secondary" href="{{ route('admin.admin-roles.index') }}">Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>{{ $record ? 'Update' : 'Save' }} Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin-module-js/access/role-permissions.js').'?v='.filemtime(public_path('admin-module-js/access/role-permissions.js')) }}"></script>
@endpush
