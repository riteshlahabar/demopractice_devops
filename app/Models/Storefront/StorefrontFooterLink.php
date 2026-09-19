<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontFooterLink extends Model
{
    protected $fillable = ['link_group', 'title', 'url', 'image_path', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
