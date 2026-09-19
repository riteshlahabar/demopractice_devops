<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'parent_id')) {
            return;
        }

        $database = DB::getDatabaseName();

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'categories'
              AND COLUMN_NAME = 'parent_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database]);

        foreach ($foreignKeys as $foreignKey) {
            DB::statement('ALTER TABLE categories DROP FOREIGN KEY `'.$foreignKey->CONSTRAINT_NAME.'`');
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('parent_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            });
        }
    }
};
