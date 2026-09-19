<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 80);
            $table->string('native_name', 120)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('languages')->insert([
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => true, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'is_default' => false, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'mr', 'name' => 'Marathi', 'native_name' => 'मराठी', 'is_default' => false, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'gu', 'name' => 'Gujarati', 'native_name' => 'ગુજરાતી', 'is_default' => false, 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pa', 'name' => 'Punjabi', 'native_name' => 'ਪੰਜਾਬੀ', 'is_default' => false, 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'te', 'name' => 'Telugu', 'native_name' => 'తెలుగు', 'is_default' => false, 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
