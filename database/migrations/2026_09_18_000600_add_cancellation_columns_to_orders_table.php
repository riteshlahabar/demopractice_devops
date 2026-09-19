<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order status now follows the sales actions, so the only status an admin sets
 * by hand is a cancellation - and that needs a reason and an audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('cancel_reason', 500)->nullable()->after('approved_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancel_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancel_reason', 'cancelled_at']);
        });
    }
};
