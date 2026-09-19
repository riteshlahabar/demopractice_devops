<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_no')->unique();
            $table->string('order_type', 30)->index();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('pending')->index();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('gst_total', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('gst_amount', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->date('invoice_date');
            $table->decimal('grand_total', 14, 2);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        Schema::create('dispatches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('dispatch_no')->unique();
            $table->string('status', 40)->default('packing')->index();
            $table->string('courier_name')->nullable();
            $table->string('tracking_no')->nullable();
            $table->string('tracking_url')->nullable();
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatches');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
