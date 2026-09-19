<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the HRMS modules from the Phase 1 specification that had no storage
 * yet: advances and loans with their EMI schedule, the holiday calendar,
 * shifts, announcements, employee documents and performance reviews.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            // 'advance' is recovered in one go; 'loan' is recovered over EMIs.
            $table->string('advance_type', 20)->default('advance')->index();
            $table->string('reference_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('recovered_amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('installments')->default(1);
            $table->decimal('emi_amount', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->date('disbursed_on')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('advance_installments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salary_advance_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('installment_no');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 40)->default('pending')->index();
            $table->timestamps();
            $table->unique(['salary_advance_id', 'installment_no']);
        });

        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->date('holiday_date')->index();
            // 'national', 'company' or 'festival'.
            $table->string('holiday_type', 30)->default('company');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['holiday_date', 'title']);
        });

        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('grace_minutes')->default(0);
            $table->unsignedSmallInteger('half_day_minutes')->default(240);
            // Day numbers (1 = Monday) that are weekly offs for this shift.
            $table->json('weekly_offs')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('shift_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->index(['salesman_id', 'effective_from']);
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('body');
            // 'all', 'salesman', 'dealer' or 'customer'.
            $table->string('audience', 20)->default('salesman')->index();
            $table->string('category', 40)->default('announcement');
            $table->string('attachment_path')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            // 'aadhaar', 'pan', 'driving_license', 'bank', 'appointment_letter',
            // 'id_card', 'certificate' or 'other'.
            $table->string('document_type', 40)->index();
            $table->string('document_no')->nullable();
            $table->string('file_path')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('performance_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('sales_score', 5, 2)->default(0);
            $table->decimal('collection_score', 5, 2)->default(0);
            $table->decimal('visit_score', 5, 2)->default(0);
            $table->decimal('overall_rating', 5, 2)->default(0);
            $table->json('kpis')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['salesman_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('advance_installments');
        Schema::dropIfExists('salary_advances');
    }
};
