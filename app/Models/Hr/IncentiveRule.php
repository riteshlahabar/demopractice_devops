<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentiveRule extends Model
{
    public const BASES = [
        'target_achievement' => 'Target Achievement %',
        'sales_value' => 'Sales Value',
        'collection_value' => 'Collection Value',
        'dealer_visits' => 'Dealer Visits',
    ];

    public const REWARD_TYPES = ['percent' => '% of the measured value', 'fixed' => 'Fixed Amount'];

    public const APPLIES_TO = [
        'all' => 'All Salesmen',
        'department' => 'One Department',
        'designation' => 'One Designation',
        'salesman' => 'One Salesman',
    ];

    protected $fillable = [
        'name', 'basis', 'slab_from', 'slab_to', 'reward_type', 'reward_value', 'max_reward',
        'applies_to', 'department_id', 'designation_id', 'salesman_id',
        'effective_from', 'effective_to', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'slab_from' => 'decimal:2', 'slab_to' => 'decimal:2',
            'reward_value' => 'decimal:2', 'max_reward' => 'decimal:2',
            'effective_from' => 'date', 'effective_to' => 'date',
            'sort_order' => 'integer', 'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function getBasisLabelAttribute(): string
    {
        return self::BASES[$this->basis] ?? $this->basis;
    }

    /**
     * Whether a measured value falls inside this slab. `slab_to` null means
     * the slab is open ended.
     */
    public function coversValue(float $value): bool
    {
        if ($value < (float) $this->slab_from) {
            return false;
        }

        return $this->slab_to === null || $value <= (float) $this->slab_to;
    }

    /**
     * Reward earned on a measured value, capped by max_reward when set.
     */
    public function rewardFor(float $measuredValue, float $baseAmount): float
    {
        $reward = $this->reward_type === 'fixed'
            ? (float) $this->reward_value
            : $baseAmount * (float) $this->reward_value / 100;

        if ($this->max_reward !== null) {
            $reward = min($reward, (float) $this->max_reward);
        }

        return round(max(0, $reward), 2);
    }
}
