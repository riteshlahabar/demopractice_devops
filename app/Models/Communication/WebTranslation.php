<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Model;

class WebTranslation extends Model
{
    protected $fillable = [
        'group',
        'translation_key',
        'locale',
        'english_text',
        'value',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
