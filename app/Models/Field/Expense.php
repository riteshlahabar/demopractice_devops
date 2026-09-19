<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['salesman_id', 'expense_type', 'expense_date', 'amount', 'status', 'receipt_path', 'remarks', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['expense_date' => 'date', 'amount' => 'decimal:2', 'approved_at' => 'datetime'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
