<?php

namespace Tests\Unit;

use App\Models\Hr\SalaryRevision;
use App\Providers\ReportServiceProvider;
use App\Services\Admin\Reports\Hrms\EmployeeReport;
use App\Services\Admin\Reports\Hrms\GpsAttendanceReport;
use App\Services\Admin\Reports\Hrms\IncentiveReport;
use App\Services\Admin\Reports\Hrms\PerformanceReport;
use App\Services\Admin\Reports\Hrms\SalaryRevisionReport;
use App\Services\Hr\SalaryRevisionService;
use Tests\TestCase;

class SalaryRevisionTest extends TestCase
{
    private function revision(array $attributes = []): SalaryRevision
    {
        return new SalaryRevision($attributes + [
            'salesman_id' => 1, 'previous_basic' => 20000, 'new_basic' => 25000,
            'effective_from' => '2026-09-01', 'reason' => 'increment',
        ]);
    }

    public function test_the_change_is_the_difference_between_the_two_amounts(): void
    {
        $this->assertSame(5000.0, $this->revision()->change_amount);
        $this->assertSame(25.0, $this->revision()->change_percent);
    }

    public function test_a_salary_cut_is_reported_as_a_negative_change(): void
    {
        $revision = $this->revision(['previous_basic' => 30000, 'new_basic' => 27000]);

        $this->assertSame(-3000.0, $revision->change_amount);
        $this->assertSame(-10.0, $revision->change_percent);
    }

    public function test_the_first_salary_has_no_percentage_to_compare_against(): void
    {
        $revision = $this->revision(['previous_basic' => null, 'new_basic' => 18000]);

        $this->assertSame(18000.0, $revision->change_amount);
        $this->assertSame(0.0, $revision->change_percent);
    }

    public function test_an_unchanged_salary_is_not_recorded(): void
    {
        // Returns before touching the database, so a no-op edit of the employee
        // record never fills the history with rows that say nothing.
        $this->assertNull((new SalaryRevisionService)->record(1, 25000.0, 25000.0));
    }

    public function test_recording_can_be_suspended_and_is_restored_afterwards(): void
    {
        $service = new SalaryRevisionService;

        $inside = $service->withoutRecording(fn (): ?SalaryRevision => $service->record(1, 20000.0, 25000.0));

        $this->assertNull($inside);
        $this->assertSame('back on', $service->withoutRecording(fn (): string => 'back on'));
    }

    public function test_an_unknown_reason_falls_back_to_other(): void
    {
        $this->assertSame('Increment', $this->revision()->reason_label);
        $this->assertSame('nonsense', $this->revision(['reason' => 'nonsense'])->reason_label);
    }

    public function test_the_new_hrms_reports_are_registered(): void
    {
        $registered = ReportServiceProvider::REPORTS;

        foreach ([PerformanceReport::class, EmployeeReport::class, GpsAttendanceReport::class, IncentiveReport::class, SalaryRevisionReport::class] as $report) {
            $this->assertContains($report, $registered);
        }

        $keys = array_map(static fn (string $report): string => (new $report)->key(), $registered);

        $this->assertSame($keys, array_unique($keys), 'Two reports share a key, so one would hide the other.');
    }
}
