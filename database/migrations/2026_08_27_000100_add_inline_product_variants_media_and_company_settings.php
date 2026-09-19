<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($index): bool => ($index->Key_name ?? null) === $indexName);
    }

    public function up(): void
    {
        if (! Schema::hasColumn('products', 'benefits')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->longText('benefits')->nullable()->after('description');
            });
        }

        if (! Schema::hasColumn('products', 'usage_instructions')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->longText('usage_instructions')->nullable()->after('benefits');
            });
        }

        if (! Schema::hasColumn('products', 'crop_information')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->longText('crop_information')->nullable()->after('usage_instructions');
            });
        }

        if (! Schema::hasColumn('product_variants', 'size_value')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->decimal('size_value', 12, 3)->nullable()->after('value');
            });
        }

        if (! Schema::hasColumn('product_variants', 'size_unit')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->string('size_unit', 20)->nullable()->after('size_value');
            });
        }

        if (! Schema::hasColumn('product_variants', 'variant_sku')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->string('variant_sku', 100)->nullable()->after('size_unit');
            });
        }

        if (! Schema::hasColumn('product_variants', 'units_per_case')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->decimal('units_per_case', 12, 3)->default(1)->after('variant_sku');
            });
        }

        if (! Schema::hasColumn('product_variants', 'mrp')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->decimal('mrp', 12, 2)->nullable()->after('units_per_case');
            });
        }

        if (! Schema::hasColumn('product_variants', 'dealer_price')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->decimal('dealer_price', 12, 2)->nullable()->after('mrp');
            });
        }

        if (! Schema::hasColumn('product_variants', 'customer_price')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->decimal('customer_price', 12, 2)->nullable()->after('dealer_price');
            });
        }

        DB::table('product_variants')
            ->orderBy('id')
            ->chunkById(100, function ($variants): void {
                $productPrices = DB::table('products')
                    ->whereIn('id', $variants->pluck('product_id')->all())
                    ->get(['id', 'mrp', 'dealer_price', 'customer_price'])
                    ->keyBy('id');

                foreach ($variants as $variant) {
                    $product = $productPrices->get($variant->product_id);
                    $updates = [];

                    if ($variant->size_value === null || $variant->size_unit === null) {
                        preg_match(
                            '/^([0-9.]+)\s*([A-Za-z]+)?/',
                            (string) $variant->value,
                            $size
                        );

                        if ($variant->size_value === null) {
                            $updates['size_value'] = $size[1] ?? null;
                        }

                        if ($variant->size_unit === null) {
                            $updates['size_unit'] = isset($size[2])
                                ? strtoupper($size[2])
                                : null;
                        }
                    }

                    if ($variant->mrp === null) {
                        $updates['mrp'] = $product?->mrp;
                    }

                    if ($variant->dealer_price === null) {
                        $updates['dealer_price'] = $product?->dealer_price;
                    }

                    if ($variant->customer_price === null) {
                        $updates['customer_price'] = $product?->customer_price;
                    }

                    if ($updates !== []) {
                        DB::table('product_variants')
                            ->where('id', $variant->id)
                            ->update($updates);
                    }
                }
            });

        /*
         * IMPORTANT:
         * MySQL may be using the old composite UNIQUE index
         * for warehouse_id foreign key.
         *
         * Create independent warehouse_id index BEFORE
         * dropping old unique index.
         */
        if (! $this->indexExists(
            'inventory_batches',
            'inventory_batches_warehouse_id_index'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->index(
                    'warehouse_id',
                    'inventory_batches_warehouse_id_index'
                );
            });
        }

        if (! Schema::hasColumn('inventory_batches', 'product_variant_id')) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            });
        }

        if (! $this->indexExists(
            'inventory_batches',
            'inventory_batches_product_id_product_variant_id_index'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->index(
                    ['product_id', 'product_variant_id'],
                    'inventory_batches_product_id_product_variant_id_index'
                );
            });
        }

        if (! $this->indexExists(
            'inventory_batches',
            'inventory_batch_variant_unique'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->unique(
                    [
                        'warehouse_id',
                        'product_id',
                        'product_variant_id',
                        'batch_no',
                    ],
                    'inventory_batch_variant_unique'
                );
            });
        }

        if ($this->indexExists(
            'inventory_batches',
            'inventory_batches_warehouse_id_product_id_batch_no_unique'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->dropUnique(
                    'inventory_batches_warehouse_id_product_id_batch_no_unique'
                );
            });
        }

        if (! Schema::hasColumn('stock_movements', 'product_variant_id')) {
            Schema::table('stock_movements', function (Blueprint $table): void {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('inventory_batch_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('order_items', 'product_variant_id')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('order_items', 'variant_name')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->string('variant_name')
                    ->nullable()
                    ->after('product_variant_id');
            });
        }

        if (! Schema::hasColumn('order_items', 'pack_quantity')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->decimal('pack_quantity', 14, 3)
                    ->nullable()
                    ->after('quantity');
            });
        }

        if (! Schema::hasColumn('order_items', 'units_per_case')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->decimal('units_per_case', 12, 3)
                    ->default(1)
                    ->after('pack_quantity');
            });
        }

        if (! Schema::hasTable('product_media')) {
            Schema::create('product_media', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')
                    ->constrained('products')
                    ->cascadeOnDelete();

                $table->string('source_type', 20)->default('upload');
                $table->string('file_path')->nullable();
                $table->text('youtube_url')->nullable();
                $table->string('title')->nullable();
                $table->string('thumbnail_path')->nullable();
                $table->string('language', 10)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('company_settings')) {
            Schema::create('company_settings', function (Blueprint $table): void {
                $table->id();

                $table->string('company_name');
                $table->string('logo_path')->nullable();
                $table->text('short_intro')->nullable();
                $table->longText('description')->nullable();
                $table->text('address')->nullable();

                $table->string('phone', 50)->nullable();
                $table->string('whatsapp', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('website')->nullable();

                $table->string('gst_number', 50)->nullable();
                $table->string('cin_number', 50)->nullable();

                $table->string('founder_name')->nullable();
                $table->string('chairman_name')->nullable();
                $table->string('managing_director_name')->nullable();

                $table->text('google_business_url')->nullable();
                $table->text('facebook_url')->nullable();
                $table->text('instagram_url')->nullable();
                $table->text('youtube_url')->nullable();

                $table->timestamps();
            });
        }

        if (
            Schema::hasTable('company_settings')
            && DB::table('company_settings')->count() === 0
        ) {
            $legacySeller = DB::table('products')
                ->whereNotNull('seller_name')
                ->where('seller_name', '<>', '')
                ->orderBy('id')
                ->first();

            if ($legacySeller) {
                DB::table('company_settings')->insert([
                    'company_name' => $legacySeller->seller_name,
                    'logo_path' => $legacySeller->seller_logo,
                    'short_intro' => $legacySeller->seller_description,
                    'description' => $legacySeller->manufacturer_description,
                    'address' => $legacySeller->seller_address,
                    'phone' => $legacySeller->seller_contact,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('product_media');

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('product_variant_id');
            });
        }

        foreach (
            ['variant_name', 'pack_quantity', 'units_per_case'] as $column
        ) {
            if (Schema::hasColumn('order_items', $column)) {
                Schema::table(
                    'order_items',
                    function (Blueprint $table) use ($column): void {
                        $table->dropColumn($column);
                    }
                );
            }
        }

        if (Schema::hasColumn('stock_movements', 'product_variant_id')) {
            Schema::table('stock_movements', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('product_variant_id');
            });
        }

        if ($this->indexExists(
            'inventory_batches',
            'inventory_batch_variant_unique'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->dropUnique('inventory_batch_variant_unique');
            });
        }

        if ($this->indexExists(
            'inventory_batches',
            'inventory_batches_product_id_product_variant_id_index'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->dropIndex(
                    'inventory_batches_product_id_product_variant_id_index'
                );
            });
        }

        if (Schema::hasColumn('inventory_batches', 'product_variant_id')) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('product_variant_id');
            });
        }

        if (! $this->indexExists(
            'inventory_batches',
            'inventory_batches_warehouse_id_product_id_batch_no_unique'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->unique(
                    ['warehouse_id', 'product_id', 'batch_no'],
                    'inventory_batches_warehouse_id_product_id_batch_no_unique'
                );
            });
        }

        if ($this->indexExists(
            'inventory_batches',
            'inventory_batches_warehouse_id_index'
        )) {
            Schema::table('inventory_batches', function (Blueprint $table): void {
                $table->dropIndex(
                    'inventory_batches_warehouse_id_index'
                );
            });
        }

        foreach ([
            'size_value',
            'size_unit',
            'variant_sku',
            'units_per_case',
            'mrp',
            'dealer_price',
            'customer_price',
        ] as $column) {
            if (Schema::hasColumn('product_variants', $column)) {
                Schema::table(
                    'product_variants',
                    function (Blueprint $table) use ($column): void {
                        $table->dropColumn($column);
                    }
                );
            }
        }

        foreach (
            ['benefits', 'usage_instructions', 'crop_information'] as $column
        ) {
            if (Schema::hasColumn('products', $column)) {
                Schema::table(
                    'products',
                    function (Blueprint $table) use ($column): void {
                        $table->dropColumn($column);
                    }
                );
            }
        }
    }
};
