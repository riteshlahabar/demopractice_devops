<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_slips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('salary_year');
            $table->unsignedTinyInteger('salary_month');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('incentives', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->string('status', 40)->default('draft');
            $table->timestamps();
            $table->unique(['salesman_id', 'salary_year', 'salary_month']);
        });

        Schema::create('app_translations', function (Blueprint $table): void {
            $table->id();
            $table->string('group')->default('app');
            $table->string('translation_key');
            $table->string('locale', 10);
            $table->text('value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['translation_key', 'locale']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('channel', 40)->default('push');
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_no')->unique();
            $table->string('subject');
            $table->text('message');
            $table->string('status', 40)->default('open')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('app_translations');
        Schema::dropIfExists('salary_slips');
    }
};
