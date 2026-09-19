<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_sections', function (Blueprint $table): void {
            if (! Schema::hasColumn('storefront_sections', 'image_path')) {
                $table->string('image_path')->nullable()->after('subtitle');
            }
        });

        Schema::table('storefront_footer_links', function (Blueprint $table): void {
            if (! Schema::hasColumn('storefront_footer_links', 'image_path')) {
                $table->string('image_path')->nullable()->after('url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('storefront_sections', function (Blueprint $table): void {
            if (Schema::hasColumn('storefront_sections', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });

        Schema::table('storefront_footer_links', function (Blueprint $table): void {
            if (Schema::hasColumn('storefront_footer_links', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
