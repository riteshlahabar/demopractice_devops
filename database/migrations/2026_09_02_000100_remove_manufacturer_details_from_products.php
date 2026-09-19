<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manufacturer Details was dropped from the product form and from the detail
 * page, so the columns backing it are removed as well. Seller / company
 * information is maintained under System -> Seller / Company Information.
 */
return new class extends Migration
{
    private const COLUMNS = ['manufacturer_title', 'manufacturer_description', 'manufacturer_details'];

    public function up(): void
    {
        $existing = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => Schema::hasColumn('products', $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table('products', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'manufacturer_title')) {
                $table->string('manufacturer_title')->nullable();
            }

            if (! Schema::hasColumn('products', 'manufacturer_description')) {
                $table->longText('manufacturer_description')->nullable();
            }

            if (! Schema::hasColumn('products', 'manufacturer_details')) {
                $table->longText('manufacturer_details')->nullable();
            }
        });
    }
};
