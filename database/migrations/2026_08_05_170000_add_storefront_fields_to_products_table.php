<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'storefront_row')) {
                $table->string('storefront_row', 100)->nullable()->after('product_type')->index();
            }
            if (! Schema::hasColumn('products', 'storefront_title')) {
                $table->string('storefront_title')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'storefront_subtitle')) {
                $table->string('storefront_subtitle')->nullable()->after('storefront_title');
            }
            if (! Schema::hasColumn('products', 'storefront_description')) {
                $table->text('storefront_description')->nullable()->after('storefront_subtitle');
            }
            if (! Schema::hasColumn('products', 'storefront_banner_image')) {
                $table->string('storefront_banner_image')->nullable()->after('storefront_description');
            }
            if (! Schema::hasColumn('products', 'additional_info')) {
                $table->text('additional_info')->nullable()->after('storefront_banner_image');
            }
            if (! Schema::hasColumn('products', 'care_instructions')) {
                $table->text('care_instructions')->nullable()->after('additional_info');
            }
            if (! Schema::hasColumn('products', 'manufacturer_details')) {
                $table->text('manufacturer_details')->nullable()->after('care_instructions');
            }
            if (! Schema::hasColumn('products', 'is_offer_active')) {
                $table->boolean('is_offer_active')->default(false)->after('manufacturer_details');
            }
            if (! Schema::hasColumn('products', 'offer_start_at')) {
                $table->dateTime('offer_start_at')->nullable()->after('is_offer_active');
            }
            if (! Schema::hasColumn('products', 'offer_end_at')) {
                $table->dateTime('offer_end_at')->nullable()->after('offer_start_at');
            }
            if (! Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_visible_to_dealers');
            }
            if (! Schema::hasColumn('products', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            foreach ([
                'storefront_row',
                'storefront_title',
                'storefront_subtitle',
                'storefront_description',
                'storefront_banner_image',
                'additional_info',
                'care_instructions',
                'manufacturer_details',
                'is_offer_active',
                'offer_start_at',
                'offer_end_at',
            ] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
