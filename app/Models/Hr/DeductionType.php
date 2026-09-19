<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionType extends Model
{
    public const CALCULATIONS = [
        'fixed' => 'Fixed Amount',
        'percent_of_basic' => '% of Basic Salary',
        'percent_of_gross' => '% of Gross Salary',
    ];

    public const STATUTORY_KINDS = [
        'none' => 'Not Statutory',
        'pf' => 'Provident Fund (PF)',
        'esi' => 'ESI',
        'professional_tax' => 'Professional Tax',
    ];

    protected $fillable = [
        'name', 'code', 'calculation_type', 'default_value', 'statutory_kind',
        'employer_share_percent', 'wage_ceiling', 'applies_to_all',
        'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_value' => 'decimal:2',
            'employer_share_percent' => 'decimal:2',
            'wage_ceiling' => 'decimal:2',
            'sort_order' => 'integer',
            'applies_to_all' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function employeeDeductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    public function isStatutory(): bool
    {
        return $this->statutory_kind !== 'none';
    }
}
