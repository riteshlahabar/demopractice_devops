<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee Management gaps and Resignation & Exit (modules 1 and 19 of the
 * Phase 1 spec). The employee record gains the department, designation,
 * reporting manager and employment status it was missing, and resignations get
 * their own table carrying the request, the notice period, the exit approval
 * and the full & final settlement in one row — they are stages of one process,
 * not separate records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salesman_profiles', function (Blueprint $table): void {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->foreignId('reporting_to')->nullable()->after('designation_id')->constrained('users')->nullOnDelete();
            // active | probation | notice_period | resigned | exited
            $table->string('employment_status', 30)->default('active')->after('joining_date');
            $table->date('confirmation_date')->nullable()->after('employment_status');
            $table->date('exit_date')->nullable()->after('confirmation_date');
        });

        Schema::create('resignations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference_no', 40)->unique();
            $table->date('resignation_date');
            $table->unsignedInteger('notice_period_days')->default(30);
            $table->date('requested_last_working_date')->nullable();
            $table->date('approved_last_working_date')->nullable();
            $table->text('reason')->nullable();
            // pending | approved | rejected | withdrawn | completed
            $table->string('status', 20)->default('pending');
            $table->boolean('notice_period_waived')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('exit_interview_notes')->nullable();

            // Full & Final settlement.
            $table->decimal('pending_salary', 12, 2)->default(0);
            $table->decimal('leave_encashment', 12, 2)->default(0);
            $table->decimal('other_dues', 12, 2)->default(0);
            $table->decimal('advance_recovery', 12, 2)->default(0);
            $table->decimal('other_recovery', 12, 2)->default(0);
            $table->decimal('settlement_amount', 12, 2)->default(0);
            // pending | settled
            $table->string('settlement_status', 20)->default('pending');
            $table->date('settled_on')->nullable();
            $table->boolean('assets_returned')->default(false);
            $table->boolean('documents_handed_over')->default(false);
            $table->text('settlement_notes')->nullable();
            $table->timestamps();
            $table->index(['salesman_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignations');

        Schema::table('salesman_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('designation_id');
            $table->dropConstrainedForeignId('reporting_to');
            $table->dropColumn(['employment_status', 'confirmation_date', 'exit_date']);
        });
    }
};
