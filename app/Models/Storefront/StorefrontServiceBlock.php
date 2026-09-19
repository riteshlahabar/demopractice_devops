<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontServiceBlock extends Model
{
    protected $fillable = ['title', 'subtitle', 'icon_path', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
