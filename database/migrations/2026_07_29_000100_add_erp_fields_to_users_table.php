<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('mobile', 20)->nullable()->unique()->after('email');
            $table->string('role', 30)->default('customer')->index()->after('password');
            $table->string('status', 30)->default('active')->index()->after('role');
            $table->timestamp('mobile_verified_at')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('mobile_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['mobile', 'role', 'status', 'mobile_verified_at', 'last_login_at']);
        });
    }
};
