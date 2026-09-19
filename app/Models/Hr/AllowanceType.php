<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllowanceType extends Model
{
    public const CALCULATIONS = [
        'fixed' => 'Fixed Amount',
        'percent_of_basic' => '% of Basic Salary',
    ];

    protected $fillable = [
        'name', 'code', 'calculation_type', 'default_value', 'is_taxable',
        'applies_to_all', 'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_value' => 'decimal:2',
            'sort_order' => 'integer',
            'is_taxable' => 'boolean',
            'applies_to_all' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function employeeAllowances(): HasMany
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    /**
     * Rupee value of this allowance for the given basic salary.
     */
    public function amountFor(float $basic, ?string $calculation = null, ?float $value = null): float
    {
        $calculation ??= $this->calculation_type;
        $value ??= (float) $this->default_value;

        return $calculation === 'percent_of_basic' ? round($basic * $value / 100, 2) : round($value, 2);
    }
}
