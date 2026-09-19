<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Districts the website delivers to, listed in the header "Your Location"
 * modal. Codes point at the LGD directory; names are stored so the list
 * needs no join.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_areas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('state_code');
            $table->string('state_name', 100);
            $table->unsignedSmallInteger('district_code')->unique();
            $table->string('district_name', 100);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_areas');
    }
};
