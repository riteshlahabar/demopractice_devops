<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanTarget extends Model
{
    protected $fillable = ['salesman_id', 'period_start', 'period_end', 'target_amount', 'achieved_amount', 'commission_percent'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'target_amount' => 'decimal:2', 'achieved_amount' => 'decimal:2', 'commission_percent' => 'decimal:2'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
