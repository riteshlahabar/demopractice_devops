<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispatch extends Model
{
    protected $fillable = ['order_id', 'dispatch_no', 'status', 'courier_name', 'tracking_no', 'tracking_url', 'current_latitude', 'current_longitude', 'dispatched_at', 'out_for_delivery_at', 'delivered_at'];

    protected function casts(): array
    {
        return ['current_latitude' => 'decimal:7', 'current_longitude' => 'decimal:7', 'dispatched_at' => 'datetime', 'out_for_delivery_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
