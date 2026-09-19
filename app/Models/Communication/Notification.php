<?php

namespace App\Models\Communication;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = ['user_id', 'channel', 'title', 'message', 'payload', 'read_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
