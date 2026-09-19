<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name', 30)->unique();
            $table->string('unit_type', 40)->default('quantity')->index();
            $table->unsignedTinyInteger('decimal_precision')->default(2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('brand_id')->constrained('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('unit_id');
        });

        Schema::dropIfExists('units');
    }
};
