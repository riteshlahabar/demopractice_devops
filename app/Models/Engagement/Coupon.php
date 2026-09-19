<?php

namespace App\Models\Engagement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_FLAT = 'flat';

    protected $fillable = [
        'code', 'title', 'description', 'discount_type', 'discount_value',
        'max_discount', 'min_order_value', 'audience', 'usage_limit',
        'usage_limit_per_user', 'used_count', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Coupons that are live right now for the given audience. A null window
     * edge means "open ended", so those rows stay eligible.
     */
    public function scopeAvailableTo(Builder $query, string $audience): Builder
    {
        return $query->where('is_active', true)
            ->whereIn('audience', [$audience, 'all'])
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /**
     * Discount this coupon yields on the given order value, already capped by
     * `max_discount` and by the order value itself so a coupon can never make
     * a total negative.
     */
    public function discountFor(float $orderValue): float
    {
        $raw = $this->discount_type === self::TYPE_PERCENT
            ? $orderValue * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        if ($this->max_discount !== null) {
            $raw = min($raw, (float) $this->max_discount);
        }

        return round(min($raw, $orderValue), 2);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }
}
