<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional Google Maps embed link for the Contact Us map. Left blank, the map
 * is built from the company address instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->string('map_embed_url', 2048)->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->dropColumn('map_embed_url');
        });
    }
};
