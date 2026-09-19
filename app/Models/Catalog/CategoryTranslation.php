<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['category_id', 'locale', 'name'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
