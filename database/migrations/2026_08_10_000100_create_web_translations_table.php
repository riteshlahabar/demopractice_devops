<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_translations', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 80)->default('web');
            $table->string('translation_key', 190);
            $table->string('locale', 10);
            $table->text('english_text');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['translation_key', 'locale']);
            $table->index(['locale', 'group', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_translations');
    }
};
