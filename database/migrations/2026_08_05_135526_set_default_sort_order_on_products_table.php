<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'sort_order')) {
            DB::table('products')->whereNull('sort_order')->update(['sort_order' => 0]);
            DB::statement('ALTER TABLE products MODIFY sort_order INT UNSIGNED NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'sort_order')) {
            DB::statement('ALTER TABLE products MODIFY sort_order INT UNSIGNED NOT NULL');
        }
    }
};
