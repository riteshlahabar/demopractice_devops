<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanAsset extends Model
{
    protected $fillable = ['salesman_id', 'asset_type', 'asset_name', 'serial_no', 'issued_on', 'returned_on', 'condition', 'status'];

    protected function casts(): array
    {
        return ['issued_on' => 'date', 'returned_on' => 'date'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
