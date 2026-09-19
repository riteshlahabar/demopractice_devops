<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* Remove wrong duplicate product assignment table from older patch if it exists. */
        Schema::dropIfExists('product_homepage_section_products');

        if (! Schema::hasTable('product_homepage_sections')) {
            Schema::create('product_homepage_sections', function (Blueprint $table): void {
                $table->id();
                $table->string('section_key', 120)->unique();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('section_type', 60)->index();
                $table->string('layout_type', 80)->nullable()->index();
                $table->string('source_type', 80)->nullable()->index();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->unsignedInteger('product_limit')->default(8);
                $table->unsignedInteger('item_limit')->default(0);
                $table->string('image_size_note')->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->dateTime('start_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_homepage_section_items')) {
            Schema::create('product_homepage_section_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('section_id')->constrained('product_homepage_sections')->cascadeOnDelete();
                $table->string('slot', 80)->nullable()->index();
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable();
                $table->text('description')->nullable();
                $table->string('highlight_text')->nullable();
                $table->string('discount_text')->nullable();
                $table->string('validity_text')->nullable();
                $table->string('coupon_code', 80)->nullable();
                $table->string('button_text')->nullable();
                $table->string('button_url')->nullable();
                $table->string('image_path')->nullable();
                $table->string('mobile_image_path')->nullable();
                $table->string('logo_image_path')->nullable();
                $table->string('icon_key')->nullable();
                $table->string('background_color', 30)->nullable();
                $table->string('text_color', 30)->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        Schema::table('categories', function (Blueprint $table): void {
            $columns = Schema::getColumnListing('categories');
            $has = fn (string $column): bool => in_array($column, $columns, true);

            if (! $has('homepage_title')) {
                $table->string('homepage_title')->nullable();
            }
            if (! $has('homepage_layout')) {
                $table->string('homepage_layout', 80)->nullable();
            }
            if (! $has('homepage_product_limit')) {
                $table->unsignedInteger('homepage_product_limit')->default(8);
            }
            if (! $has('homepage_sort_order')) {
                $table->unsignedInteger('homepage_sort_order')->default(0)->index();
            }
            if (! $has('show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(false)->index();
            }
            if (! $has('image_path')) {
                $table->string('image_path')->nullable();
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            $columns = Schema::getColumnListing('products');
            $has = fn (string $column): bool => in_array($column, $columns, true);

            if (! $has('short_description')) {
                $table->text('short_description')->nullable();
            }
            if (! $has('additional_info')) {
                $table->longText('additional_info')->nullable();
            }
            if (! $has('care_instructions')) {
                $table->longText('care_instructions')->nullable();
            }
            if (! $has('manufacturer_details')) {
                $table->longText('manufacturer_details')->nullable();
            }

            if (! $has('detail_banner_image')) {
                $table->string('detail_banner_image')->nullable();
            }
            if (! $has('detail_banner_url')) {
                $table->string('detail_banner_url')->nullable();
            }
            if (! $has('detail_sidebar_banner_image')) {
                $table->string('detail_sidebar_banner_image')->nullable();
            }
            if (! $has('detail_sidebar_banner_url')) {
                $table->string('detail_sidebar_banner_url')->nullable();
            }

            if (! $has('seller_name')) {
                $table->string('seller_name')->nullable();
            }
            if (! $has('seller_logo')) {
                $table->string('seller_logo')->nullable();
            }
            if (! $has('seller_description')) {
                $table->text('seller_description')->nullable();
            }
            if (! $has('seller_address')) {
                $table->string('seller_address')->nullable();
            }
            if (! $has('seller_contact')) {
                $table->string('seller_contact', 80)->nullable();
            }

            if (! $has('manufacturer_title')) {
                $table->string('manufacturer_title')->nullable();
            }
            if (! $has('manufacturer_description')) {
                $table->longText('manufacturer_description')->nullable();
            }

            if (! $has('sale_badge_text')) {
                $table->string('sale_badge_text', 80)->nullable();
            }
            if (! $has('sold_quantity')) {
                $table->unsignedInteger('sold_quantity')->nullable();
            }
            if (! $has('total_quantity')) {
                $table->unsignedInteger('total_quantity')->nullable();
            }
            if (! $has('low_stock_text')) {
                $table->string('low_stock_text')->nullable();
            }

            if (! $has('is_top_selling')) {
                $table->boolean('is_top_selling')->default(false)->index();
            }
            if (! $has('is_trending')) {
                $table->boolean('is_trending')->default(false)->index();
            }
            if (! $has('is_new_arrival')) {
                $table->boolean('is_new_arrival')->default(false)->index();
            }
            if (! $has('is_offer_product')) {
                $table->boolean('is_offer_product')->default(false)->index();
            }
            if (! $has('is_deal_timer_product')) {
                $table->boolean('is_deal_timer_product')->default(false)->index();
            }
            if (! $has('show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(true)->index();
            }

            if (! $has('meta_title')) {
                $table->string('meta_title')->nullable();
            }
            if (! $has('meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (! $has('meta_keywords')) {
                $table->string('meta_keywords')->nullable();
            }
        });

        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('group_name', 80)->default('Weight');
                $table->string('value', 120);
                $table->decimal('price_difference', 12, 2)->default(0);
                $table->decimal('stock_quantity', 12, 3)->nullable();
                $table->boolean('is_default')->default(false);
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_related_products')) {
            Schema::create('product_related_products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
                $table->unique(['product_id', 'related_product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related_products');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_homepage_section_items');
        Schema::dropIfExists('product_homepage_sections');
    }
};
