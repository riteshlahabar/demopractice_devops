<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryAdvance extends Model
{
    public const TYPE_ADVANCE = 'advance';

    public const TYPE_LOAN = 'loan';

    protected $fillable = [
        'salesman_id', 'advance_type', 'reference_no', 'amount', 'recovered_amount',
        'installments', 'emi_amount', 'reason', 'status', 'disbursed_on',
        'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'recovered_amount' => 'decimal:2',
            'emi_amount' => 'decimal:2',
            'installments' => 'integer',
            'disbursed_on' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function getOutstandingAmountAttribute(): float
    {
        return round((float) $this->amount - (float) $this->recovered_amount, 2);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(AdvanceInstallment::class);
    }
}
