<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('internal_expense_subcategories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('internal_expense_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['category_id', 'name']);
            $table->unique(['category_id', 'code']);
        });

        Schema::create('internal_expenses', function (Blueprint $table): void {
            $table->id();
            $table->string('expense_no')->unique();
            $table->foreignId('category_id')->nullable()->constrained('internal_expense_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('internal_expense_subcategories')->nullOnDelete();
            $table->date('expense_date')->index();
            $table->string('title');
            $table->string('vendor_name')->nullable();
            $table->string('payment_mode', 40)->default('cash')->index();
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('draft')->index();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_expenses');
        Schema::dropIfExists('internal_expense_subcategories');
        Schema::dropIfExists('internal_expense_categories');
    }
};
