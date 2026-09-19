<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    protected $fillable = ['name', 'description'];

    protected function casts(): array
    {
        return ['is_super' => 'boolean'];
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(AdminRolePermission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
