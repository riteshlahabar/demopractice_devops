<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_visible_to_dealers')->index();
            }

            if (! Schema::hasColumn('products', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_featured')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('products', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
