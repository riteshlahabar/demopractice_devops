<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin panel roles. A role holds one permission row per sidebar section
 * (View / Add / Edit / Delete). A Super Admin role has full access and needs
 * no rows; every existing admin account is moved into it so nobody is locked
 * out when this runs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_super')->default(false);
            $table->timestamps();
        });

        Schema::create('admin_role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_role_id')->constrained()->cascadeOnDelete();
            $table->string('section', 100);
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['admin_role_id', 'section']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('admin_role_id')->nullable()->after('role')->constrained('admin_roles')->nullOnDelete();
        });

        $superId = DB::table('admin_roles')->insertGetId([
            'name' => 'Super Admin',
            'description' => 'Full access to every section.',
            'is_super' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('role', 'admin')->update(['admin_role_id' => $superId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('admin_role_id');
        });
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admin_roles');
    }
};
