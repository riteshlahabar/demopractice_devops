<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\SalesmanProfile;
use App\Models\User;
use App\Services\Admin\Reports\Report;

final class EmployeeReport extends Report
{
    public function key(): string
    {
        return 'employees';
    }

    public function title(): string
    {
        return 'Employee';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Employee master: department, designation, reporting manager, employment status and service dates.';
    }

    public function icon(): string
    {
        return 'users';
    }

    /**
     * A master list, not a period: the date range would only hide employees.
     */
    public function filters(): array
    {
        return ['salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $employees = User::query()
            ->where('role', User::ROLE_SALESMAN)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->whereKey($id))
            ->with(['salesmanProfile.department:id,name', 'salesmanProfile.designation:id,name'])
            ->orderBy('name')
            ->limit(self::MAX_ROWS)
            ->get();

        $managers = $this->userNames($employees->pluck('salesmanProfile.reporting_to'));

        $rows = $employees->map(function (User $employee) use ($managers): array {
            $profile = $employee->salesmanProfile;

            return [
                'code' => $profile?->employee_code,
                'name' => $employee->name,
                'mobile' => $employee->mobile,
                'department' => $profile?->department?->name,
                'designation' => $profile?->designation?->name,
                'reporting_to' => $profile?->reporting_to ? ($managers[$profile->reporting_to] ?? null) : null,
                'joining_date' => $profile?->joining_date,
                'confirmation_date' => $profile?->confirmation_date,
                'exit_date' => $profile?->exit_date,
                'basic' => (float) ($profile?->basic_salary ?? 0),
                // The account can be blocked while the employment record still
                // says active, so both are shown rather than merged into one.
                'employment_status' => SalesmanProfile::EMPLOYMENT_STATUSES[$profile?->employment_status] ?? ($profile?->employment_status ?? 'Active'),
                'status' => $employee->status,
            ];
        })->all();

        $counts = array_count_values(array_column($rows, 'employment_status'));

        return new ReportResult(
            cards: [
                ['label' => 'Employees', 'icon' => 'users', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Active', 'icon' => 'user-check', 'tone' => 'primary', 'value' => $counts['Active'] ?? 0, 'type' => 'number'],
                ['label' => 'On Notice', 'icon' => 'user-minus', 'tone' => 'warning', 'value' => $counts['Notice Period'] ?? 0, 'type' => 'number'],
                ['label' => 'Exited', 'icon' => 'user-x', 'tone' => 'danger', 'value' => ($counts['Exited'] ?? 0) + ($counts['Resigned'] ?? 0), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'code', 'label' => 'Employee Code'],
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'designation', 'label' => 'Designation'],
                ['key' => 'reporting_to', 'label' => 'Reporting To'],
                ['key' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date'],
                ['key' => 'confirmation_date', 'label' => 'Confirmed On', 'type' => 'date'],
                ['key' => 'exit_date', 'label' => 'Exit Date', 'type' => 'date'],
                ['key' => 'basic', 'label' => 'Basic Salary', 'type' => 'money'],
                ['key' => 'employment_status', 'label' => 'Employment'],
                ['key' => 'status', 'label' => 'Account', 'type' => 'status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
