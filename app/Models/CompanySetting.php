<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name', 'logo_path', 'short_intro', 'description', 'address',
        'phone', 'whatsapp', 'email', 'website', 'map_embed_url', 'gst_number', 'cin_number',
        'founder_name', 'chairman_name', 'managing_director_name',
        'google_business_url', 'facebook_url', 'instagram_url', 'youtube_url',
    ];

    /**
     * Google Maps embed address for the Contact page: the saved link when there
     * is one, otherwise a map of the company address. Null hides the map.
     */
    public function mapEmbedUrl(): ?string
    {
        $saved = trim((string) $this->map_embed_url);

        if ($saved !== '') {
            return $saved;
        }

        $address = trim(preg_replace('/\s+/', ' ', (string) $this->address) ?? '');

        return $address === '' ? null : 'https://www.google.com/maps?q='.rawurlencode($address).'&output=embed';
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        return Str::startsWith($this->logo_path, ['http://', 'https://'])
            ? $this->logo_path
            : asset($this->logo_path);
    }
}
