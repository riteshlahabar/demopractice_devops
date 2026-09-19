<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_homepage_section_items')) {
            return;
        }

        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_homepage_section_items', 'offer_image_path')) {
                $table->string('offer_image_path')->nullable()->after('logo_image_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_homepage_section_items')) {
            return;
        }

        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            if (Schema::hasColumn('product_homepage_section_items', 'offer_image_path')) {
                $table->dropColumn('offer_image_path');
            }
        });
    }
};
