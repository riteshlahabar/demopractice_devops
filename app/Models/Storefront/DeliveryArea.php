<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    protected $fillable = ['state_code', 'state_name', 'district_code', 'district_name', 'min_order_amount', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'state_code' => 'integer',
            'district_code' => 'integer',
            'min_order_amount' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
