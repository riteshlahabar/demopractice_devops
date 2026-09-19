<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Public path of the photo uploaded from the app's Account screen.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'profile_photo')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('profile_photo')->nullable()->after('mobile');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'profile_photo')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('profile_photo');
        });
    }
};
