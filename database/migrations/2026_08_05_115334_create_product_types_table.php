<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'product_type_id')) {
                $table->foreignId('product_type_id')->nullable()->after('brand_id')->constrained('product_types')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'product_type_id')) {
                $table->dropConstrainedForeignId('product_type_id');
            }
        });

        Schema::dropIfExists('product_types');
    }
};
