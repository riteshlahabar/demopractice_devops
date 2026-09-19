<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where each app translation came from: website, app, google or manual.
 * Manual rows are never overwritten by the admin Translate button.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('app_translations', 'source')) {
            return;
        }

        Schema::table('app_translations', function (Blueprint $table): void {
            $table->string('source', 20)->nullable()->after('value');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_translations', 'source')) {
            Schema::table('app_translations', function (Blueprint $table): void {
                $table->dropColumn('source');
            });
        }
    }
};
