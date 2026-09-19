<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPlan extends Model
{
    protected $fillable = ['salesman_id', 'plan_date', 'route_name', 'dealer_ids', 'status'];

    protected function casts(): array
    {
        return ['plan_date' => 'date', 'dealer_ids' => 'array'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
