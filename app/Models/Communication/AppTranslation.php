<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Model;

class AppTranslation extends Model
{
    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_APP = 'app';

    public const SOURCE_GOOGLE = 'google';

    public const SOURCE_MANUAL = 'manual';

    protected $fillable = ['app', 'group', 'translation_key', 'english_text', 'locale', 'value', 'source', 'is_active'];

    protected static function booted(): void
    {
        // Any save that goes through model events is an admin edit; the
        // Translate run writes quietly and sets its own source.
        static::saving(function (self $translation): void {
            if ($translation->isDirty('value') && ! $translation->isDirty('source')) {
                $translation->source = self::SOURCE_MANUAL;
            }
        });
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
