<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_homepage_sections') && Schema::hasColumn('product_homepage_sections', 'section_key')) {
            DB::statement('ALTER TABLE `product_homepage_sections` MODIFY `section_key` VARCHAR(120) NULL');
        }
    }

    public function down(): void
    {
        // Keep nullable for safety. section_key is generated automatically by the model.
    }
};
