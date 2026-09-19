<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = ['order_id', 'user_id', 'return_no', 'reason', 'status', 'refund_amount', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['refund_amount' => 'decimal:2', 'approved_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
