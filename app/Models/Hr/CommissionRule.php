<?php

namespace App\Models\Hr;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRule extends Model
{
    public const BASES = [
        'sales_value' => 'All Sales Value',
        'product' => 'One Product',
        'category' => 'One Category',
    ];

    public const APPLIES_TO = ['all' => 'All Salesmen', 'salesman' => 'One Salesman'];

    protected $fillable = [
        'name', 'basis', 'product_id', 'category_id', 'commission_percent',
        'min_sales_value', 'max_commission', 'applies_to', 'salesman_id',
        'effective_from', 'effective_to', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2', 'min_sales_value' => 'decimal:2',
            'max_commission' => 'decimal:2', 'effective_from' => 'date', 'effective_to' => 'date',
            'sort_order' => 'integer', 'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
     * Commission on a sales value, or zero when the value has not reached the
     * rule's minimum. Capped by max_commission when set.
     */
    public function commissionFor(float $salesValue): float
    {
        if ($salesValue < (float) $this->min_sales_value) {
            return 0.0;
        }

        $commission = $salesValue * (float) $this->commission_percent / 100;

        if ($this->max_commission !== null) {
            $commission = min($commission, (float) $this->max_commission);
        }

        return round(max(0, $commission), 2);
    }
}
