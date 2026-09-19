<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_banners', function (Blueprint $table): void {
            $table->id();
            $table->string('placement', 80)->index();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('storefront_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('section_key', 120)->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('section_type', 40)->default('product')->index();
            $table->string('source_type', 40)->default('manual')->index();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('product_limit')->default(12);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('storefront_section_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->constrained('storefront_sections')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['section_id', 'product_id']);
        });

        Schema::create('storefront_service_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('storefront_footer_links', function (Blueprint $table): void {
            $table->id();
            $table->string('link_group', 80)->index();
            $table->string('title');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_footer_links');
        Schema::dropIfExists('storefront_service_blocks');
        Schema::dropIfExists('storefront_section_products');
        Schema::dropIfExists('storefront_sections');
        Schema::dropIfExists('storefront_banners');
    }
};
