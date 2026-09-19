<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the "Out for delivery" checkpoint between dispatched and delivered.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dispatches', 'out_for_delivery_at')) {
            return;
        }

        Schema::table('dispatches', function (Blueprint $table): void {
            $table->timestamp('out_for_delivery_at')->nullable()->after('dispatched_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('dispatches', 'out_for_delivery_at')) {
            return;
        }

        Schema::table('dispatches', function (Blueprint $table): void {
            $table->dropColumn('out_for_delivery_at');
        });
    }
};
