<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payment_no')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_mode', 40);
            $table->string('status', 40)->default('pending')->index();
            $table->decimal('amount', 14, 2);
            $table->string('transaction_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dealer_statements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dealer_id')->constrained('users')->cascadeOnDelete();
            $table->string('entry_type', 40)->index();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('salesman_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('target_amount', 14, 2);
            $table->decimal('achieved_amount', 14, 2)->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman_targets');
        Schema::dropIfExists('dealer_statements');
        Schema::dropIfExists('payments');
    }
};
