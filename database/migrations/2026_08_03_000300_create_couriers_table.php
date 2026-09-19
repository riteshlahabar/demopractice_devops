<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table): void {
            $table->id();
            $table->string('courier_code', 50)->unique();
            $table->string('name');
            $table->string('mobile', 20)->unique();
            $table->string('alternate_mobile', 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('company_name')->nullable();
            $table->string('vehicle_type', 40)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->string('license_number', 80)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('service_area')->nullable();
            $table->string('id_proof_type', 40)->nullable();
            $table->string('id_proof_number', 80)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
