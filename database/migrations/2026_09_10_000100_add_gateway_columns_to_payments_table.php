<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            // Which gateway handled it. Kept apart from payment_mode, which
            // holds what the payer actually used (card, upi, netbanking).
            $table->string('gateway', 40)->nullable()->after('payment_mode')->index();

            // The verified callback, kept verbatim. When a payer disputes a
            // charge months later this is the only record of what the bank
            // actually said.
            $table->json('gateway_payload')->nullable()->after('transaction_ref');

            $table->string('failure_reason')->nullable()->after('gateway_payload');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['gateway', 'gateway_payload', 'failure_reason']);
        });
    }
};
