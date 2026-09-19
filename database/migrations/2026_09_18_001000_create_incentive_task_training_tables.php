<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Incentive & Commission Rules (module 11), Task Management and Training
 * Management (module 16) of the Phase 1 spec.
 *
 * Incentive rules are slabs read against a salesman's target achievement or
 * sales value; commission rules are a percentage on what was sold, optionally
 * narrowed to one product or category. Both are rules only — the monthly
 * incentive and commission figures they produce are written onto the existing
 * salary slip, so no second ledger is introduced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            // target_achievement | sales_value | collection_value | dealer_visits
            $table->string('basis', 40)->default('target_achievement');
            $table->decimal('slab_from', 14, 2)->default(0);
            $table->decimal('slab_to', 14, 2)->nullable();
            // percent | fixed
            $table->string('reward_type', 20)->default('percent');
            $table->decimal('reward_value', 12, 2)->default(0);
            $table->decimal('max_reward', 12, 2)->nullable();
            // all | department | designation | salesman
            $table->string('applies_to', 20)->default('all');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['basis', 'is_active']);
        });

        Schema::create('commission_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            // sales_value | product | category
            $table->string('basis', 40)->default('sales_value');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('commission_percent', 6, 2)->default(0);
            $table->decimal('min_sales_value', 14, 2)->default(0);
            $table->decimal('max_commission', 12, 2)->nullable();
            $table->string('applies_to', 20)->default('all');
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['basis', 'is_active']);
        });

        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained('users')->nullOnDelete();
            // low | normal | high | urgent
            $table->string('priority', 20)->default('normal');
            // pending | in_progress | completed | cancelled
            $table->string('status', 20)->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('training_programs', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('trainer')->nullable();
            // classroom | online | on_the_job
            $table->string('mode', 30)->default('classroom');
            $table->string('venue')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('duration_hours')->default(0);
            $table->text('description')->nullable();
            // planned | ongoing | completed | cancelled
            $table->string('status', 20)->default('planned');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('training_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            // enrolled | attended | absent | completed
            $table->string('status', 20)->default('enrolled');
            $table->decimal('score', 6, 2)->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->string('certificate_path', 2048)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['training_program_id', 'salesman_id']);
        });

        Schema::create('employee_skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->string('skill');
            // beginner | intermediate | advanced | expert
            $table->string('level', 20)->default('beginner');
            $table->date('certified_on')->nullable();
            $table->string('certified_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['salesman_id', 'skill']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('training_attendances');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('incentive_rules');
    }
};
