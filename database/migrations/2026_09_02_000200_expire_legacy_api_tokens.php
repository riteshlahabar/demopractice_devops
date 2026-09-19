<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * API tokens used to be issued without an expiry, so a token leaked once stayed
 * valid forever. The guard now rejects tokens with no expiry; giving the
 * existing ones a normal lifetime keeps people signed in on their phones
 * instead of logging everyone out at deploy time.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('api_tokens')
            ->whereNull('expires_at')
            ->update([
                'expires_at' => now()->addDays((int) config('erp_auth.token.lifetime_days', 30)),
            ]);
    }

    public function down(): void
    {
        // Nothing to undo: removing the expiry again would re-open the issue.
    }
};
