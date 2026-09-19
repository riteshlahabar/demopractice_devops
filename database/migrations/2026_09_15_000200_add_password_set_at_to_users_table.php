<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When the user chose their own password. Accounts created by mobile OTP get
     * a random one they never saw, so this stays null until they set one.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'password_set_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('password_set_at')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'password_set_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('password_set_at');
        });
    }
};
