<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->unique(['category_id', 'locale']);
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('product_type', 40)->default('medicine')->index();
            $table->string('hsn_code')->nullable();
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('mrp', 12, 2)->default(0);
            $table->decimal('customer_price', 12, 2)->default(0);
            $table->decimal('dealer_price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_visible_to_customers')->default(true);
            $table->boolean('is_visible_to_dealers')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unique(['product_id', 'locale']);
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
    }
};
