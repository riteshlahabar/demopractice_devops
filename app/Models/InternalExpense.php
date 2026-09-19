<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InternalExpense extends Model
{
    protected $fillable = ['expense_no', 'category_id', 'subcategory_id', 'expense_date', 'title', 'vendor_name', 'payment_mode', 'taxable_amount', 'gst_amount', 'total_amount', 'paid_by', 'status', 'receipt_path', 'notes'];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'taxable_amount' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InternalExpense $expense): void {
            if (! $expense->expense_no) {
                $expense->expense_no = 'EXP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InternalExpenseCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(InternalExpenseSubcategory::class, 'subcategory_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
