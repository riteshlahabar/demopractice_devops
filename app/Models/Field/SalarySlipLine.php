<?php

namespace App\Models\Field;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One allowance or deduction line of a generated payslip. Kept as its own row
 * so a payslip can still be explained after the allowance or deduction type it
 * came from has been edited or switched off.
 */
class SalarySlipLine extends Model
{
    protected $fillable = ['salary_slip_id', 'kind', 'source', 'source_id', 'label', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'source_id' => 'integer'];
    }

    public function salarySlip(): BelongsTo
    {
        return $this->belongsTo(SalarySlip::class);
    }
}
