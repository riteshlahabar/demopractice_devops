<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_no');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('quantity', 14, 3)->default(0);
            $table->decimal('reserved_quantity', 14, 3)->default(0);
            $table->decimal('low_stock_alert', 14, 3)->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_id', 'batch_no']);
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_batch_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type', 40)->index();
            $table->decimal('quantity', 14, 3);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_batches');
        Schema::dropIfExists('warehouses');
    }
};
