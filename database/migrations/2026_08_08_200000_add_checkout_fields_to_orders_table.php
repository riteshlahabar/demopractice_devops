<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('contact_name')->nullable()->after('notes');
            $table->string('contact_mobile', 20)->nullable()->after('contact_name');
            $table->string('address_type', 30)->nullable()->after('contact_mobile');
            $table->string('address_line1')->nullable()->after('address_type');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city', 100)->nullable()->after('address_line2');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('pincode', 12)->nullable()->after('state');
            $table->string('payment_method', 30)->nullable()->after('pincode');
            $table->string('payment_status', 30)->default('pending')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'contact_name',
                'contact_mobile',
                'address_type',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'pincode',
                'payment_method',
                'payment_status',
            ]);
        });
    }
};
