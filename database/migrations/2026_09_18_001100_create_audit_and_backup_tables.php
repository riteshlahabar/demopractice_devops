<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit Logs and Backup & Restore — the last two admin modules of the Phase 1
 * spec. Audit rows record who changed what, storing only the changed columns
 * rather than a whole record copy, and never store a value from a redacted
 * column (passwords, tokens). Backups record what was taken, how big it was
 * and whether it finished, so a failed run is visible instead of silent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role', 40)->nullable();
            // created | updated | deleted
            $table->string('event', 20);
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('label')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('event');
        });

        Schema::create('backups', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('disk', 40)->default('local');
            $table->string('path', 2048);
            // database
            $table->string('type', 20)->default('database');
            // running | completed | failed
            $table->string('status', 20)->default('running');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('table_count')->default(0);
            $table->text('error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('audit_logs');
    }
};
