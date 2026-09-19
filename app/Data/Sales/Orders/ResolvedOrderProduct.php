<?php

namespace App\Data\Sales\Orders;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

final readonly class ResolvedOrderProduct
{
    public function __construct(
        public Product $product,
        public ?ProductVariant $variant
    ) {}
}
