<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which languages each mobile app offers in its language picker.
 * A missing row means "on", so every language is active by default and a
 * language added later appears in all apps without touching this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_languages', function (Blueprint $table): void {
            $table->id();
            $table->string('app', 20);
            $table->string('locale', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['app', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_languages');
    }
};
