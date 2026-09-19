<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerStatement extends Model
{
    protected $fillable = ['dealer_id', 'entry_type', 'debit', 'credit', 'balance', 'reference_type', 'reference_id', 'remarks'];

    protected function casts(): array
    {
        return ['debit' => 'decimal:2', 'credit' => 'decimal:2', 'balance' => 'decimal:2'];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}
