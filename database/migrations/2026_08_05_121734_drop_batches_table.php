<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('batches');
    }

    public function down(): void
    {
        // Old separate batches module removed.
    }
};
