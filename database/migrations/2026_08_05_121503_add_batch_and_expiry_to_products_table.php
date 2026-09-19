<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'batch_no')) {
                $table->string('batch_no')->nullable()->after('sku');
            }

            if (! Schema::hasColumn('products', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('batch_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }

            if (Schema::hasColumn('products', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });
    }
};
