<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductType $type): void {
            if (blank($type->slug) && filled($type->name)) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
