<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Messages that slide in the website header top bar, managed from
 * Settings -> Top Bar Messages. Seeded with the two texts the header used to
 * hardcode (minus the template coupon code) so nothing changes on upload.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_topbar_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('heading')->nullable();
            $table->string('message', 500)->nullable();
            $table->string('link_label', 100)->nullable();
            $table->string('link_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();

        DB::table('storefront_topbar_messages')->insert([
            [
                'heading' => 'Welcome to Bawaskar Farmer Store!',
                'message' => 'Wrap new offers/gift every single day on Weekends.',
                'link_label' => null,
                'link_url' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'heading' => null,
                'message' => 'Something you love is now on sale!',
                'link_label' => 'Buy Now !',
                'link_url' => '/shop-left-sidebar',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_topbar_messages');
    }
};
