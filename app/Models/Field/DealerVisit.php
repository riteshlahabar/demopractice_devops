<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerVisit extends Model
{
    protected $fillable = ['salesman_id', 'dealer_id', 'visited_at', 'latitude', 'longitude', 'purpose', 'remarks'];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}
