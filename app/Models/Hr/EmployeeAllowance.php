<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAllowance extends Model
{
    protected $fillable = [
        'salesman_id', 'allowance_type_id', 'calculation_type', 'amount',
        'effective_from', 'effective_to', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceType::class);
    }
}
