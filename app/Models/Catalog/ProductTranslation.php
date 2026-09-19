<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['product_id', 'locale', 'name', 'description'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
