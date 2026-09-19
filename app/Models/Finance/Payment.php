<?php

namespace App\Models\Finance;

use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['payment_no', 'order_id', 'payer_id', 'collected_by', 'payment_mode', 'status', 'amount', 'transaction_ref', 'paid_at', 'gateway', 'gateway_payload', 'failure_reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'gateway_payload' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
