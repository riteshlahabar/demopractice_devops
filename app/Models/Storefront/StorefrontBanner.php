<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontBanner extends Model
{
    protected $fillable = [
        'placement',
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_url',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
