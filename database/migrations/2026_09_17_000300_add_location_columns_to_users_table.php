<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registered location for dealers, customers and salesmen. Codes point at the
 * lgd_* directory; names are stored too so lists, invoices and exports never
 * need a lookup, and so a taluka typed by hand ("Other") still has a name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedSmallInteger('state_code')->nullable()->index();
            $table->string('state_name', 100)->nullable();
            $table->unsignedInteger('district_code')->nullable()->index();
            $table->string('district_name', 150)->nullable();
            $table->string('subdistrict_code', 20)->nullable();
            $table->string('subdistrict_name', 150)->nullable();
            $table->string('city_village', 150)->nullable();
            $table->string('pincode', 6)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['state_code']);
            $table->dropIndex(['district_code']);
            $table->dropIndex(['pincode']);
            $table->dropColumn([
                'state_code', 'state_name', 'district_code', 'district_name',
                'subdistrict_code', 'subdistrict_name', 'city_village', 'pincode',
            ]);
        });
    }
};
