<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Model;

class AppLanguage extends Model
{
    protected $fillable = ['app', 'locale', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
