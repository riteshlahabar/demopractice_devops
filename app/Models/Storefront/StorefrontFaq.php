<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontFaq extends Model
{
    protected $fillable = ['question', 'answer', 'category', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Answer split into paragraphs so the page keeps the template's <p> blocks. */
    public function answerParagraphs(): array
    {
        $parts = preg_split('/\R{2,}/', trim((string) $this->answer)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $part): bool => $part !== ''));
    }

    /** Readable category name for the admin list; the stored value is the config key. */
    public function getCategoryLabelAttribute(): string
    {
        $category = trim((string) $this->category);

        return $category === '' ? '' : (string) (config('storefront.faq_categories.'.$category.'.label') ?: $category);
    }
}
