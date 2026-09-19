<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('working_minutes')->default(0);
            $table->string('status', 40)->default('present');
            $table->timestamps();
            $table->unique(['salesman_id', 'attendance_date']);
        });

        Schema::create('dealer_visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dealer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('visited_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->string('expense_type', 40);
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->string('status', 40)->default('pending')->index();
            $table->string('receipt_path')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->string('leave_type', 40);
            $table->date('from_date');
            $table->date('to_date');
            $table->text('reason')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('salesman_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->string('asset_type', 40);
            $table->string('asset_name');
            $table->string('serial_no')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('returned_on')->nullable();
            $table->string('condition')->nullable();
            $table->string('status', 40)->default('issued')->index();
            $table->timestamps();
        });

        Schema::create('tour_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->date('plan_date')->index();
            $table->string('route_name');
            $table->json('dealer_ids')->nullable();
            $table->string('status', 40)->default('planned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_plans');
        Schema::dropIfExists('salesman_assets');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('dealer_visits');
        Schema::dropIfExists('attendance_logs');
    }
};
