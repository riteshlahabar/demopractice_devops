<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftAssignment extends Model
{
    protected $fillable = ['salesman_id', 'shift_id', 'effective_from', 'effective_to'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_to' => 'date'];
    }

    /**
     * The assignment covering today: started already and either open ended or
     * not yet finished.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereDate('effective_from', '<=', now())
            ->where(fn (Builder $q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', now()));
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
