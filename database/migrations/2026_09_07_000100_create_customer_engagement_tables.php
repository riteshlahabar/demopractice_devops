<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs the B2C engagement modules the mobile app exposes: a persistent
 * wishlist, product reviews and coupon-based offers.
 *
 * The storefront already had a session-scoped wishlist; the app needs one that
 * survives a reinstall and follows the account across devices, so it lives in
 * its own table keyed by user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('product_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Set when the review is tied to a delivered order, which is what
            // lets the storefront show a "verified purchase" badge.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->timestamps();
            $table->unique(['product_id', 'user_id']);
        });

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            // 'percent' or 'flat'.
            $table->string('discount_type', 20)->default('percent');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->decimal('min_order_value', 12, 2)->default(0);
            // 'customer', 'dealer' or 'all'.
            $table->string('audience', 20)->default('customer')->index();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('wishlists');
    }
};
