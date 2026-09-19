<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One table for all three mobile apps: `app` keeps customer, dealer and
 * salesman strings apart, `english_text` is the source the admin Translate
 * button works from, and `value` may be empty until a row is translated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_translations', function (Blueprint $table): void {
            $table->string('app', 20)->nullable()->after('id')->index();
            $table->text('english_text')->nullable()->after('translation_key');
        });

        Schema::table('app_translations', function (Blueprint $table): void {
            $table->text('value')->nullable()->change();
            $table->dropUnique(['translation_key', 'locale']);
            $table->unique(['app', 'translation_key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('app_translations', function (Blueprint $table): void {
            $table->dropUnique(['app', 'translation_key', 'locale']);
            $table->unique(['translation_key', 'locale']);
            $table->dropColumn(['app', 'english_text']);
        });
    }
};
