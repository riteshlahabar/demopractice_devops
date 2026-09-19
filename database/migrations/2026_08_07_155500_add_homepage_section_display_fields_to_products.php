<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $columns = Schema::getColumnListing('products');
            $has = fn (string $column): bool => in_array($column, $columns, true);

            if (! $has('homepage_section_id')) {
                $table->foreignId('homepage_section_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('product_homepage_sections')
                    ->nullOnDelete();
            }

            if (! $has('homepage_title')) {
                $table->string('homepage_title')->nullable();
            }
            if (! $has('homepage_subtitle')) {
                $table->string('homepage_subtitle')->nullable();
            }
            if (! $has('homepage_description')) {
                $table->text('homepage_description')->nullable();
            }

            if (! $has('homepage_image_path')) {
                $table->string('homepage_image_path')->nullable();
            }
            if (! $has('homepage_mobile_image_path')) {
                $table->string('homepage_mobile_image_path')->nullable();
            }
            if (! $has('homepage_logo_image_path')) {
                $table->string('homepage_logo_image_path')->nullable();
            }
            if (! $has('homepage_offer_image_path')) {
                $table->string('homepage_offer_image_path')->nullable();
            }

            if (! $has('homepage_highlight_text')) {
                $table->string('homepage_highlight_text')->nullable();
            }
            if (! $has('homepage_discount_text')) {
                $table->string('homepage_discount_text')->nullable();
            }
            if (! $has('homepage_validity_text')) {
                $table->string('homepage_validity_text')->nullable();
            }
            if (! $has('homepage_coupon_code')) {
                $table->string('homepage_coupon_code', 80)->nullable();
            }

            if (! $has('homepage_button_text')) {
                $table->string('homepage_button_text')->nullable();
            }
            if (! $has('homepage_button_url')) {
                $table->string('homepage_button_url')->nullable();
            }

            if (! $has('homepage_icon_key')) {
                $table->string('homepage_icon_key')->nullable();
            }
            if (! $has('homepage_slot')) {
                $table->string('homepage_slot', 80)->nullable();
            }

            if (! $has('homepage_background_color')) {
                $table->string('homepage_background_color', 30)->nullable();
            }
            if (! $has('homepage_text_color')) {
                $table->string('homepage_text_color', 30)->nullable();
            }

            if (! $has('homepage_sort_order')) {
                $table->unsignedInteger('homepage_sort_order')->default(0)->index();
            }
        });

        if (Schema::hasTable('product_homepage_sections') && Schema::hasColumn('products', 'homepage_section_id')) {
            DB::statement("
                UPDATE products p
                SET homepage_section_id = (
                    SELECT hs.id
                    FROM product_homepage_sections hs
                    WHERE hs.section_type = 'product_section'
                      AND hs.source_type = 'category_products'
                      AND hs.category_id = p.category_id
                    ORDER BY hs.sort_order ASC, hs.id ASC
                    LIMIT 1
                )
                WHERE p.homepage_section_id IS NULL
                  AND EXISTS (
                    SELECT 1
                    FROM product_homepage_sections hs2
                    WHERE hs2.section_type = 'product_section'
                      AND hs2.source_type = 'category_products'
                      AND hs2.category_id = p.category_id
                  )
            ");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $columns = Schema::getColumnListing('products');
            $has = fn (string $column): bool => in_array($column, $columns, true);

            if ($has('homepage_section_id')) {
                $table->dropConstrainedForeignId('homepage_section_id');
            }

            foreach ([
                'homepage_title',
                'homepage_subtitle',
                'homepage_description',
                'homepage_image_path',
                'homepage_mobile_image_path',
                'homepage_logo_image_path',
                'homepage_offer_image_path',
                'homepage_highlight_text',
                'homepage_discount_text',
                'homepage_validity_text',
                'homepage_coupon_code',
                'homepage_button_text',
                'homepage_button_url',
                'homepage_icon_key',
                'homepage_slot',
                'homepage_background_color',
                'homepage_text_color',
                'homepage_sort_order',
            ] as $column) {
                if ($has($column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
