<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Salary revision history. `salesman_profiles.basic_salary` holds only the
 * current figure, so every change to it was previously lost; this table keeps
 * the old and the new amount with the date it took effect, which is what a
 * payroll dispute and an appointment/increment letter both need.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            // Null on the very first record, when there was nothing before it.
            $table->decimal('previous_basic', 12, 2)->nullable();
            $table->decimal('new_basic', 12, 2);
            $table->date('effective_from');
            // increment | promotion | correction | annual | other
            $table->string('reason', 40)->default('increment');
            $table->string('notes', 500)->nullable();
            // manual = entered on this screen, profile = picked up from an edit
            // of the employee record.
            $table->string('source', 20)->default('manual');
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
            // Null until the amount has been written to the employee record;
            // a future-dated revision is stamped when it comes due.
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['salesman_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_revisions');
    }
};
