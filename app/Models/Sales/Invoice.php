<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'invoice_no', 'invoice_date', 'grand_total', 'pdf_path'];

    protected function casts(): array
    {
        return ['invoice_date' => 'date', 'grand_total' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
