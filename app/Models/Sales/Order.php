<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'order_type',
        'customer_id',
        'dealer_id',
        'salesman_id',
        'approved_by',
        'status',
        'subtotal',
        'gst_total',
        'discount_total',
        'grand_total',
        'notes',
        'contact_name',
        'contact_mobile',
        'address_type',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'pincode',
        'payment_method',
        'payment_status',
        'approved_at',
        'cancel_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'gst_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function proformaInvoices(): HasMany
    {
        return $this->hasMany(ProformaInvoice::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }
}
