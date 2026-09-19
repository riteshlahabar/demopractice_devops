<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_variants', 'unit_id')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->foreignId('unit_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('units')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('product_variants', 'hsn_code')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->string('hsn_code', 40)->nullable()->after('variant_sku');
            });
        }

        if (! Schema::hasColumn('product_variants', 'gst_percent')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->decimal('gst_percent', 5, 2)->nullable()->after('hsn_code');
            });
        }

        if (! Schema::hasColumn('products', 'detail_banner_position')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('detail_banner_position', 20)
                    ->default('after')
                    ->after('detail_banner_url');
            });
        }

        $this->copyLegacyProductDetailsToVariants();
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'detail_banner_position')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('detail_banner_position');
            });
        }

        foreach (['gst_percent', 'hsn_code'] as $column) {
            if (Schema::hasColumn('product_variants', $column)) {
                Schema::table('product_variants', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }

        if (Schema::hasColumn('product_variants', 'unit_id')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('unit_id');
            });
        }
    }

    private function copyLegacyProductDetailsToVariants(): void
    {
        DB::table('product_variants')
            ->orderBy('id')
            ->chunkById(100, function ($variants): void {
                $products = DB::table('products')
                    ->whereIn('id', $variants->pluck('product_id')->all())
                    ->get(['id', 'unit_id', 'sku', 'hsn_code', 'gst_percent'])
                    ->keyBy('id');

                foreach ($variants as $variant) {
                    $product = $products->get($variant->product_id);
                    if (! $product) {
                        continue;
                    }

                    $updates = [
                        'unit_id' => $variant->unit_id ?? $product->unit_id,
                        'hsn_code' => $variant->hsn_code ?? $product->hsn_code,
                        'gst_percent' => $variant->gst_percent ?? $product->gst_percent,
                    ];

                    if ($variant->is_default && blank($variant->variant_sku)) {
                        $updates['variant_sku'] = $product->sku;
                    }

                    DB::table('product_variants')->where('id', $variant->id)->update($updates);
                }
            });
    }
};
