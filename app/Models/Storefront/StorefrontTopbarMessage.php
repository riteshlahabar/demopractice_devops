<?php

namespace App\Models\Storefront;

use Illuminate\Database\Eloquent\Model;

class StorefrontTopbarMessage extends Model
{
    protected $fillable = ['heading', 'message', 'link_label', 'link_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Full URL for the link; paths such as /shop-left-sidebar are made absolute. */
    public function linkHref(): ?string
    {
        $url = trim((string) $this->link_url);

        if ($url === '') {
            return null;
        }

        return preg_match('#^(https?:|mailto:|tel:)#i', $url) ? $url : url($url);
    }
}
