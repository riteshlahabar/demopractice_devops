@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="card admin-form-card">
    <div class="card-body pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form class="row g-2 align-items-end mb-3" method="GET" action="{{ route('admin.attendance.bulk') }}">
            <div class="col-md-3">
                <label class="form-label">Attendance Date</label>
                <input class="form-control" type="date" name="attendance_date" value="{{ $date }}">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-outline-primary" type="submit"><i class="iconoir-search me-1"></i>Load</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.attendance.bulk.store') }}">
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="bulk-select-col">Mark</th>
                            <th>Employee</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Working Minutes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesmen as $index => $salesman)
                            @php($row = $existing[$salesman->id] ?? null)
                            <tr>
                                <td class="bulk-select-col">
                                    <input type="hidden" name="rows[{{ $index }}][salesman_id]" value="{{ $salesman->id }}">
                                    <input class="form-check-input" type="checkbox" name="rows[{{ $index }}][mark]" value="1" checked>
                                </td>
                                <td>{{ $salesman->name }}</td>
                                <td>{{ $salesman->mobile ?? '-' }}</td>
                                <td style="min-width:150px">
                                    <select class="form-select" name="rows[{{ $index }}][status]">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" @selected(($row->status ?? 'present') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width:130px"><input class="form-control" type="time" name="rows[{{ $index }}][check_in_time]" value="{{ $row?->check_in_at?->format('H:i') }}"></td>
                                <td style="min-width:130px"><input class="form-control" type="time" name="rows[{{ $index }}][check_out_time]" value="{{ $row?->check_out_at?->format('H:i') }}"></td>
                                <td style="min-width:140px"><input class="form-control" type="number" min="0" max="1440" name="rows[{{ $index }}][working_minutes]" value="{{ $row->working_minutes ?? 0 }}"></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No active salesmen found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-outline-secondary" href="{{ route('admin.attendance.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save Bulk Attendance</button>
            </div>
        </form>
    </div>
</div>
@endsection