<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HRMS Settings (module 21 of the Phase 1 spec): the masters every other HR
 * module reads. Departments and designations describe the org chart, leave
 * policies replace the hardcoded entitlements in config/hrms.php, allowance
 * and deduction types drive payroll, and approval workflows record who signs
 * off on each kind of request. Attendance and salary rules are a single
 * settings row because there is only ever one of each.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('designations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->unsignedInteger('level')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_policies', function (Blueprint $table): void {
            $table->id();
            $table->string('leave_type', 40)->unique();
            $table->string('label');
            $table->decimal('annual_days', 6, 2)->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('carry_forward')->default(false);
            $table->decimal('max_carry_forward_days', 6, 2)->default(0);
            $table->unsignedInteger('min_notice_days')->default(0);
            $table->unsignedInteger('max_consecutive_days')->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('allowance_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            // fixed = flat rupees, percent_of_basic = share of basic salary.
            $table->string('calculation_type', 30)->default('fixed');
            $table->decimal('default_value', 12, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('applies_to_all')->default(false);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('deduction_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            // fixed | percent_of_basic | percent_of_gross
            $table->string('calculation_type', 30)->default('fixed');
            $table->decimal('default_value', 12, 2)->default(0);
            // none | pf | esi | professional_tax — statutory rows are computed
            // by StatutoryDeductionCalculator instead of the plain formula.
            $table->string('statutory_kind', 30)->default('none');
            $table->decimal('employer_share_percent', 6, 2)->default(0);
            $table->decimal('wage_ceiling', 12, 2)->nullable();
            $table->boolean('applies_to_all')->default(false);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_workflows', function (Blueprint $table): void {
            $table->id();
            // leave | expense | advance | tour_plan | salary
            $table->string('request_type', 40);
            $table->unsignedInteger('level')->default(1);
            // admin | salesman_manager — the role that signs off at this level.
            $table->string('approver_role', 40)->default('admin');
            $table->decimal('amount_from', 14, 2)->nullable();
            $table->decimal('amount_to', 14, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['request_type', 'level']);
        });

        Schema::create('hrms_settings', function (Blueprint $table): void {
            $table->id();
            // Attendance rules.
            $table->unsignedInteger('grace_minutes')->default(10);
            $table->unsignedInteger('half_day_minutes')->default(240);
            $table->unsignedInteger('full_day_minutes')->default(480);
            $table->unsignedInteger('late_marks_per_absent')->default(3);
            $table->boolean('auto_mark_absent')->default(false);
            $table->json('default_weekly_offs')->nullable();
            // Salary rules.
            $table->string('working_days_basis', 20)->default('calendar');
            $table->unsignedInteger('fixed_working_days')->default(26);
            $table->unsignedInteger('payroll_cycle_day')->default(1);
            $table->boolean('deduct_unpaid_leave')->default(true);
            $table->boolean('deduct_absent_days')->default(true);
            $table->string('rounding_mode', 20)->default('nearest');
            $table->timestamps();
        });

        $now = now();
        DB::table('hrms_settings')->insert([
            'default_weekly_offs' => json_encode(['sunday']),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Seed the leave policies from the entitlements that were hardcoded in
        // config/hrms.php, so nothing changes for existing salesmen on upgrade.
        $leave = [
            ['casual', 'Casual Leave', 12, true, 1],
            ['sick', 'Sick Leave', 8, true, 2],
            ['earned', 'Earned Leave', 15, true, 3],
            ['unpaid', 'Unpaid Leave', 0, false, 4],
        ];
        foreach ($leave as [$type, $label, $days, $paid, $sort]) {
            DB::table('leave_policies')->insert([
                'leave_type' => $type, 'label' => $label, 'annual_days' => $days,
                'is_paid' => $paid, 'sort_order' => $sort,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $allowances = [
            ['Travel Allowance', 'travel', 'fixed', 0, 1],
            ['Fuel Allowance', 'fuel', 'fixed', 0, 2],
            ['Mobile Allowance', 'mobile', 'fixed', 0, 3],
            ['Daily Allowance (DA)', 'da', 'percent_of_basic', 0, 4],
            ['Other Allowance', 'other', 'fixed', 0, 5],
        ];
        foreach ($allowances as [$name, $code, $calc, $value, $sort]) {
            DB::table('allowance_types')->insert([
                'name' => $name, 'code' => $code, 'calculation_type' => $calc,
                'default_value' => $value, 'sort_order' => $sort,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $deductions = [
            ['Provident Fund (PF)', 'pf', 'percent_of_basic', 12, 'pf', 12, 15000, true, 1],
            ['ESI', 'esi', 'percent_of_gross', 0.75, 'esi', 3.25, 21000, true, 2],
            ['Professional Tax', 'professional_tax', 'fixed', 200, 'professional_tax', 0, null, true, 3],
            ['Other Deduction', 'other', 'fixed', 0, 'none', 0, null, false, 4],
        ];
        foreach ($deductions as [$name, $code, $calc, $value, $kind, $employer, $ceiling, $all, $sort]) {
            DB::table('deduction_types')->insert([
                'name' => $name, 'code' => $code, 'calculation_type' => $calc,
                'default_value' => $value, 'statutory_kind' => $kind,
                'employer_share_percent' => $employer, 'wage_ceiling' => $ceiling,
                'applies_to_all' => $all, 'sort_order' => $sort,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (['leave', 'expense', 'advance', 'tour_plan'] as $type) {
            DB::table('approval_workflows')->insert([
                'request_type' => $type, 'level' => 1, 'approver_role' => 'admin',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hrms_settings');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('deduction_types');
        Schema::dropIfExists('allowance_types');
        Schema::dropIfExists('leave_policies');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
