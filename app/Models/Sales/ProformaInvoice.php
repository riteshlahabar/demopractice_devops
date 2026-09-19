<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProformaInvoice extends Model
{
    protected $fillable = ['order_id', 'proforma_no', 'proforma_date', 'valid_until', 'subtotal', 'gst_total', 'discount_total', 'grand_total', 'status', 'notes', 'pdf_path'];

    protected function casts(): array
    {
        return [
            'proforma_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'gst_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
