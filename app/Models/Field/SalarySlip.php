<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalarySlip extends Model
{
    protected $fillable = ['salesman_id', 'salary_year', 'salary_month', 'basic_salary', 'gross_salary', 'allowances', 'bonus', 'incentives', 'commission', 'deductions', 'employer_contribution', 'payable_days', 'working_days', 'net_salary', 'status'];

    protected function casts(): array
    {
        return ['basic_salary' => 'decimal:2', 'gross_salary' => 'decimal:2', 'allowances' => 'decimal:2', 'bonus' => 'decimal:2', 'incentives' => 'decimal:2', 'commission' => 'decimal:2', 'deductions' => 'decimal:2', 'employer_contribution' => 'decimal:2', 'payable_days' => 'decimal:2', 'working_days' => 'decimal:2', 'net_salary' => 'decimal:2'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalarySlipLine::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
