<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontTeamMember extends Model
{
    protected $fillable = ['name', 'role', 'bio', 'photo_path', 'facebook_url', 'instagram_url', 'twitter_url', 'linkedin_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Social profiles that are filled in, with the template's icon and colour class.
     *
     * @return array<int, array{url: string, icon: string, class: string}>
     */
    public function socialLinks(): array
    {
        return array_values(array_filter([
            ['url' => (string) $this->facebook_url, 'icon' => 'fa-brands fa-facebook-f', 'class' => 'fb-bg'],
            ['url' => (string) $this->twitter_url, 'icon' => 'fa-brands fa-twitter', 'class' => 'twitter-bg'],
            ['url' => (string) $this->instagram_url, 'icon' => 'fa-brands fa-instagram', 'class' => 'insta-bg'],
            ['url' => (string) $this->linkedin_url, 'icon' => 'fa-brands fa-linkedin-in', 'class' => 'linkedin-bg'],
        ], static fn (array $link): bool => $link['url'] !== ''));
    }
}
