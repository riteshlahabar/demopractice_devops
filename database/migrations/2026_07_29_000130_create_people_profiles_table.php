<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesman_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_code')->unique();
            $table->date('joining_date')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('target_amount', 14, 2)->default(0);
            $table->string('territory')->nullable();
            $table->timestamps();
        });

        Schema::create('dealer_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dealer_code')->unique();
            $table->string('firm_name');
            $table->string('gst_number')->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->decimal('outstanding_balance', 14, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('customer_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('preferred_language', 10)->default('en');
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('shipping');
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 12);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('dealer_profiles');
        Schema::dropIfExists('salesman_profiles');
    }
};
