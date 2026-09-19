<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceInstallment extends Model
{
    protected $fillable = [
        'salary_advance_id', 'installment_no', 'due_date', 'amount', 'paid_amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'installment_no' => 'integer',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(SalaryAdvance::class, 'salary_advance_id');
    }
}
