<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allowance and Deduction Management (modules 7 and 8 of the Phase 1 spec).
 * Each row assigns one allowance or deduction type to one salesman for a date
 * range, optionally overriding the type's default amount. `salary_slip_lines`
 * records what payroll actually charged, so a payslip can be explained line by
 * line long after the underlying types have been edited.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_allowances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('allowance_type_id')->constrained()->cascadeOnDelete();
            // Null = use the type's own calculation_type / default_value.
            $table->string('calculation_type', 30)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('notes', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['salesman_id', 'is_active']);
        });

        Schema::create('employee_deductions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->constrained()->cascadeOnDelete();
            $table->string('calculation_type', 30)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('notes', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['salesman_id', 'is_active']);
        });

        Schema::create('salary_slip_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salary_slip_id')->constrained()->cascadeOnDelete();
            // allowance | deduction
            $table->string('kind', 20);
            // allowance_type | deduction_type | advance | attendance | manual
            $table->string('source', 40)->default('manual');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('label');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['salary_slip_id', 'kind']);
        });

        Schema::table('salary_slips', function (Blueprint $table): void {
            $table->decimal('gross_salary', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('employer_contribution', 12, 2)->default(0)->after('deductions');
            $table->decimal('payable_days', 6, 2)->nullable()->after('employer_contribution');
            $table->decimal('working_days', 6, 2)->nullable()->after('payable_days');
        });
    }

    public function down(): void
    {
        Schema::table('salary_slips', function (Blueprint $table): void {
            $table->dropColumn(['gross_salary', 'employer_contribution', 'payable_days', 'working_days']);
        });
        Schema::dropIfExists('salary_slip_lines');
        Schema::dropIfExists('employee_deductions');
        Schema::dropIfExists('employee_allowances');
    }
};
