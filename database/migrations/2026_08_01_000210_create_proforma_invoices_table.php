<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proforma_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('proforma_no')->unique();
            $table->date('proforma_date');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('gst_total', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('status', 40)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proforma_invoices');
    }
};
