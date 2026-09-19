<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontAboutItem extends Model
{
    public const BLOCK_HIGHLIGHT = 'highlight';

    public const BLOCK_STAT = 'stat';

    protected $fillable = ['block', 'icon_path', 'value', 'title', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Readable block name for the admin list. */
    public function getBlockLabelAttribute(): string
    {
        return $this->block === self::BLOCK_STAT ? 'What We Do Figure' : 'Intro Bullet';
    }
}
