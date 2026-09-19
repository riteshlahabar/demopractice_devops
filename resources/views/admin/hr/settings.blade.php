@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.hrms-settings.update') }}">
                    @csrf
                    @method('PUT')

                    @php
                        $weeklyOffs = old('default_weekly_offs', $setting->default_weekly_offs ?? []);
                    @endphp

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">Attendance Rules</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grace Period (minutes) <span class="text-danger">*</span></label>
                            <input class="form-control @error('grace_minutes')is-invalid @enderror" type="number" name="grace_minutes" min="0" max="240" value="{{ old('grace_minutes', $setting->grace_minutes) }}" required>
                            <div class="form-text">Late check-in allowed before a late mark is recorded.</div>
                            @error('grace_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Full Day (minutes) <span class="text-danger">*</span></label>
                            <input class="form-control @error('full_day_minutes')is-invalid @enderror" type="number" name="full_day_minutes" min="1" max="1440" value="{{ old('full_day_minutes', $setting->full_day_minutes) }}" required>
                            @error('full_day_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Half Day (minutes) <span class="text-danger">*</span></label>
                            <input class="form-control @error('half_day_minutes')is-invalid @enderror" type="number" name="half_day_minutes" min="0" max="1440" value="{{ old('half_day_minutes', $setting->half_day_minutes) }}" required>
                            <div class="form-text">Worked less than this and the day is a half day.</div>
                            @error('half_day_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Late Marks = 1 Absent <span class="text-danger">*</span></label>
                            <input class="form-control @error('late_marks_per_absent')is-invalid @enderror" type="number" name="late_marks_per_absent" min="0" max="31" value="{{ old('late_marks_per_absent', $setting->late_marks_per_absent) }}" required>
                            <div class="form-text">0 turns the rule off.</div>
                            @error('late_marks_per_absent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Default Weekly Offs</label>
                            <div class="d-flex flex-wrap gap-3 border rounded px-3 py-2">
                                @foreach($weekdays as $value => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="weekly-off-{{ $value }}" name="default_weekly_offs[]" value="{{ $value }}" @checked(in_array($value, (array) $weeklyOffs, true))>
                                        <label class="form-check-label" for="weekly-off-{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Used when a shift does not set its own weekly offs.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_mark_absent" name="auto_mark_absent" value="1" @checked(old('auto_mark_absent', $setting->auto_mark_absent))>
                                <label class="form-check-label" for="auto_mark_absent">Mark a salesman absent automatically when there is no check-in</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">Salary Rules</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Working Days Basis <span class="text-danger">*</span></label>
                            <select class="form-select @error('working_days_basis')is-invalid @enderror" name="working_days_basis" required>
                                @foreach($workingDaysBasis as $value => $label)
                                    <option value="{{ $value }}" @selected(old('working_days_basis', $setting->working_days_basis) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('working_days_basis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fixed Working Days <span class="text-danger">*</span></label>
                            <input class="form-control @error('fixed_working_days')is-invalid @enderror" type="number" name="fixed_working_days" min="1" max="31" value="{{ old('fixed_working_days', $setting->fixed_working_days) }}" required>
                            <div class="form-text">Used only when the basis above is "Fixed days per month".</div>
                            @error('fixed_working_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payroll Cycle Day <span class="text-danger">*</span></label>
                            <input class="form-control @error('payroll_cycle_day')is-invalid @enderror" type="number" name="payroll_cycle_day" min="1" max="28" value="{{ old('payroll_cycle_day', $setting->payroll_cycle_day) }}" required>
                            @error('payroll_cycle_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Net Salary Rounding <span class="text-danger">*</span></label>
                            <select class="form-select @error('rounding_mode')is-invalid @enderror" name="rounding_mode" required>
                                @foreach($roundingModes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('rounding_mode', $setting->rounding_mode) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('rounding_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label d-block">Loss of Pay</label>
                            <div class="d-flex flex-wrap gap-4 border rounded px-3 py-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="deduct_absent_days" name="deduct_absent_days" value="1" @checked(old('deduct_absent_days', $setting->deduct_absent_days))>
                                    <label class="form-check-label" for="deduct_absent_days">Deduct absent days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="deduct_unpaid_leave" name="deduct_unpaid_leave" value="1" @checked(old('deduct_unpaid_leave', $setting->deduct_unpaid_leave))>
                                    <label class="form-check-label" for="deduct_unpaid_leave">Deduct approved leave on unpaid leave types</label>
                                </div>
                            </div>
                            <div class="form-text">Both off means every salesman is paid the full basic regardless of attendance.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save HRMS Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
