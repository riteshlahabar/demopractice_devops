<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerProfile extends Model
{
    protected $fillable = ['user_id', 'salesman_id', 'dealer_code', 'firm_name', 'gst_number', 'credit_limit', 'outstanding_balance', 'approved_at', 'approved_by'];

    protected function casts(): array
    {
        return ['credit_limit' => 'decimal:2', 'outstanding_balance' => 'decimal:2', 'approved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
