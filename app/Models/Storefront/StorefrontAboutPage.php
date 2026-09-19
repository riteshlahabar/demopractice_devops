<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontAboutPage extends Model
{
    protected $table = 'storefront_about_page';

    protected $fillable = [
        'intro_label', 'intro_heading', 'intro_text',
        'image_one_path', 'image_two_path',
        'stats_label', 'stats_heading', 'team_label', 'team_heading',
    ];

    /** Intro copy split into paragraphs so the page keeps the template's <p> blocks. */
    public function introParagraphs(): array
    {
        $parts = preg_split('/\R{2,}/', trim((string) $this->intro_text)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $part): bool => $part !== ''));
    }
}
