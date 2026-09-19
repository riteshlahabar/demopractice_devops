<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One change of an employee's basic salary. Kept as its own row so the history
 * survives the next edit of the employee record.
 */
class SalaryRevision extends Model
{
    public const REASONS = [
        'increment' => 'Increment',
        'promotion' => 'Promotion',
        'annual' => 'Annual Revision',
        'correction' => 'Correction',
        'other' => 'Other',
    ];

    protected $fillable = [
        'salesman_id', 'previous_basic', 'new_basic', 'effective_from',
        'reason', 'notes', 'source', 'revised_by', 'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'previous_basic' => 'decimal:2',
            'new_basic' => 'decimal:2',
            'applied_at' => 'datetime',
        ];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function reviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    /**
     * What the revision added, negative when the salary was cut.
     */
    public function getChangeAmountAttribute(): float
    {
        return round((float) $this->new_basic - (float) $this->previous_basic, 2);
    }

    public function getChangePercentAttribute(): float
    {
        $previous = (float) $this->previous_basic;

        return $previous > 0 ? round($this->change_amount / $previous * 100, 2) : 0.0;
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? (string) $this->reason;
    }
}
