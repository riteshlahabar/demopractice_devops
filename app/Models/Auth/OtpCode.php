<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['mobile', 'purpose', 'code_hash', 'attempts', 'expires_at', 'verified_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
